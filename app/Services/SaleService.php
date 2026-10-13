<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\CashSession;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private readonly NumberSequenceService $numberSequences,
        private readonly StockMovementService $stockMovements,
        private readonly CashSessionService $cashSessions,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function createDraft(User $user, array $data, array $lines): Sale
    {
        return DB::transaction(function () use ($user, $data, $lines) {
            $sale = Sale::query()->create([
                'company_id' => $user->company_id,
                'stock_id' => $data['stock_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'status' => SaleStatus::Draft,
                'sale_date' => $data['sale_date'],
                'currency_id' => $data['currency_id'],
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->syncLines($sale, $lines);
            $this->recalculateTotals($sale);

            return $sale->fresh(['items.product', 'customer', 'stock', 'currency']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>|null  $lines
     */
    public function updateDraft(Sale $sale, array $data, ?array $lines = null): Sale
    {
        $this->assertDraft($sale);

        return DB::transaction(function () use ($sale, $data, $lines) {
            $sale->update($data);

            if ($lines !== null) {
                $sale->items()->delete();
                $this->syncLines($sale, $lines);
            }

            $this->recalculateTotals($sale->fresh());

            return $sale->fresh(['items.product', 'customer', 'stock', 'currency']);
        });
    }

    public function validate(Sale $sale, User $user): Sale
    {
        $this->assertDraft($sale);

        if ($sale->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => ['La vente doit contenir au moins une ligne.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $user) {
            $sale = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();
            $this->assertDraft($sale);
            $sale->load(['items', 'customer']);

            $movement = $this->stockMovements->createDraft($user, [
                'movement_type' => StockMovementType::Sale->value,
                'source_stock_id' => $sale->stock_id,
                'document_date' => $sale->sale_date->toDateTimeString(),
                'reason' => 'Vente '.$sale->id,
            ], $sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
            ])->all());

            $movement = $this->stockMovements->validate($movement, $user);

            $invoiceNumber = $this->numberSequences->next(
                $user->company_id,
                'invoice',
                'INV-',
                $sale->stock_id,
            );

            Invoice::query()->create([
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $sale->sale_date,
                'status' => InvoiceStatus::Issued,
                'customer_name_snapshot' => $sale->customer?->name ?? 'Client comptoir',
                'customer_address_snapshot' => $sale->customer?->address,
                'customer_tax_number_snapshot' => $sale->customer?->tax_number,
                'subtotal' => $sale->subtotal,
                'discount_amount' => $sale->discount_amount,
                'tax_amount' => $sale->tax_amount,
                'total_amount' => $sale->total_amount,
                'currency_id' => $sale->currency_id,
                'issued_by' => $user->id,
            ]);

            $sale->update([
                'invoice_number' => $invoiceNumber,
                'status' => SaleStatus::Validated,
                'stock_movement_id' => $movement->id,
                'validated_by' => $user->id,
                'validated_at' => now(),
                'balance_due' => $sale->total_amount,
                'amount_paid' => 0,
            ]);

            $sale = $sale->fresh(['items.product', 'customer', 'stock', 'currency', 'invoice', 'payments', 'stockMovement']);
            $this->auditLogger->log('sale.validated', $sale, null, $sale->toArray());

            return $sale;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addPayment(Sale $sale, User $user, array $data): Payment
    {
        if (! $sale->acceptsPayments()) {
            throw ValidationException::withMessages([
                'sale' => ['Les paiements ne sont acceptés que pour une vente validée.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $user, $data) {
            $sale = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if (! $sale->acceptsPayments()) {
                throw ValidationException::withMessages([
                    'sale' => ['Les paiements ne sont acceptés que pour une vente validée.'],
                ]);
            }

            $amount = number_format((float) $data['amount'], 4, '.', '');
            if (bccomp($amount, '0', 4) <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Le montant du paiement doit être positif.'],
                ]);
            }

            $balanceDue = number_format((float) $sale->balance_due, 4, '.', '');

            // Si le montant reçu dépasse le solde (monnaie à rendre / écart flottant),
            // on enregistre uniquement le solde restant.
            if (bccomp($amount, $balanceDue, 4) > 0) {
                $amount = $balanceDue;
            }

            if (bccomp($amount, '0', 4) <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Aucun solde restant à encaisser.'],
                ]);
            }

            $cashSessionId = $data['cash_session_id'] ?? null;
            $cashSession = null;

            if ($cashSessionId) {
                $cashSession = CashSession::query()->findOrFail($cashSessionId);
                if (! $cashSession->isOpen()) {
                    throw ValidationException::withMessages([
                        'cash_session_id' => ['La session de caisse doit être ouverte.'],
                    ]);
                }
            }

            $payment = Payment::query()->create([
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'payment_method_id' => $data['payment_method_id'],
                'currency_id' => $data['currency_id'] ?? $sale->currency_id,
                'amount' => $amount,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'reference' => $data['reference'] ?? null,
                'payment_date' => $data['payment_date'],
                'status' => PaymentStatus::Confirmed,
                'cash_session_id' => $cashSession?->id,
                'received_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($cashSession) {
                $this->cashSessions->recordMovement(
                    $cashSession,
                    $user,
                    CashMovementType::In,
                    $amount,
                    'Paiement vente '.($sale->invoice_number ?? '#'.$sale->id),
                    $payment,
                );
            }

            $this->refreshPaymentTotals($sale);
            $this->auditLogger->log('sale.payment_added', $sale, null, $payment->toArray());

            return $payment->fresh(['paymentMethod', 'currency']);
        });
    }

    public function cancel(Sale $sale, User $user, ?string $reason = null): Sale
    {
        if ($sale->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Supprimez le brouillon plutôt que de l’annuler.'],
            ]);
        }

        if ($sale->isCancelled()) {
            throw ValidationException::withMessages([
                'status' => ['Cette vente est déjà annulée.'],
            ]);
        }

        return DB::transaction(function () use ($sale, $user, $reason) {
            $sale = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->stock_movement_id) {
                $movement = $sale->stockMovement;
                if ($movement && $movement->isValidated()) {
                    $this->stockMovements->cancel($movement, $user, $reason ?? 'Annulation vente');
                }
            }

            $sale->payments()
                ->where('status', PaymentStatus::Confirmed)
                ->update(['status' => PaymentStatus::Cancelled]);

            $sale->invoice?->update(['status' => InvoiceStatus::Cancelled]);

            $sale->update([
                'status' => SaleStatus::Cancelled,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'amount_paid' => 0,
                'balance_due' => $sale->total_amount,
            ]);

            $sale = $sale->fresh(['items.product', 'customer', 'invoice', 'payments', 'stockMovement']);
            $this->auditLogger->log('sale.cancelled', $sale, null, [
                'reason' => $reason,
                'sale' => $sale->toArray(),
            ]);

            return $sale;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncLines(Sale $sale, array $lines): void
    {
        foreach ($lines as $line) {
            $product = Product::query()
                ->with('productLabel')
                ->findOrFail($line['product_id']);

            $quantity = (string) $line['quantity'];
            $unitPrice = (string) ($line['unit_price'] ?? $product->selling_price);
            $discount = (string) ($line['discount_amount'] ?? 0);

            // TVA toujours prise depuis le produit (étiquette), jamais depuis la vente.
            $label = $product->productLabel;
            $taxRate = $label && ! $label->is_tax_exempt
                ? (string) $label->default_tax_rate
                : '0.0000';

            $gross = bcmul($quantity, $unitPrice, 4);
            $lineSubtotal = bcsub($gross, $discount, 4);
            $taxAmount = bcdiv(bcmul($lineSubtotal, $taxRate, 4), '100', 4);
            $lineTotal = bcadd($lineSubtotal, $taxAmount, 4);

            $sale->items()->create([
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'product_sku_snapshot' => $product->sku,
                'label_name_snapshot' => $product->productLabel->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'tax_rate_snapshot' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_subtotal' => $lineSubtotal,
                'line_total' => $lineTotal,
            ]);
        }
    }

    private function recalculateTotals(Sale $sale): void
    {
        $sale->load('items');

        $subtotal = '0.0000';
        $taxAmount = '0.0000';

        foreach ($sale->items as $item) {
            $subtotal = bcadd($subtotal, (string) $item->line_subtotal, 4);
            $taxAmount = bcadd($taxAmount, (string) $item->tax_amount, 4);
        }

        $headerDiscount = (string) $sale->discount_amount;
        $total = bcsub(bcadd($subtotal, $taxAmount, 4), $headerDiscount, 4);

        if (bccomp($total, '0', 4) < 0) {
            $total = '0.0000';
        }

        $sale->update([
            'subtotal' => $subtotal,
            'discount_amount' => $headerDiscount,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'balance_due' => $total,
            'amount_paid' => 0,
        ]);
    }

    private function refreshPaymentTotals(Sale $sale): void
    {
        $paid = (string) $sale->payments()
            ->where('status', PaymentStatus::Confirmed)
            ->sum('amount');

        $balance = bcsub((string) $sale->total_amount, $paid, 4);

        $status = match (true) {
            bccomp($paid, '0', 4) <= 0 => SaleStatus::Validated,
            bccomp($balance, '0', 4) <= 0 => SaleStatus::Paid,
            default => SaleStatus::PartiallyPaid,
        };

        $sale->update([
            'amount_paid' => $paid,
            'balance_due' => bccomp($balance, '0', 4) < 0 ? '0.0000' : $balance,
            'status' => $status,
        ]);
    }

    private function assertDraft(Sale $sale): void
    {
        if (! $sale->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Seul un brouillon peut être modifié ou validé.'],
            ]);
        }
    }
}
