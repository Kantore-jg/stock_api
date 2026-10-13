<?php

namespace App\Services;

use App\Enums\StockMovementStatus;
use App\Enums\StockMovementType;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\StockMovementLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    public function __construct(
        private readonly NumberSequenceService $numberSequences,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $lines
     */
    public function createDraft(User $user, array $data, array $lines): StockMovement
    {
        $type = StockMovementType::from($data['movement_type']);
        $this->assertStocksForType($type, $data);

        return DB::transaction(function () use ($user, $data, $lines, $type) {
            $reference = $data['reference']
                ?? $this->numberSequences->next(
                    $user->company_id,
                    $type->documentType(),
                    $type->defaultPrefix(),
                );

            $movement = StockMovement::query()->create([
                'company_id' => $user->company_id,
                'reference' => $reference,
                'movement_type' => $type,
                'status' => StockMovementStatus::Draft,
                'source_stock_id' => $data['source_stock_id'] ?? null,
                'destination_stock_id' => $data['destination_stock_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'document_date' => $data['document_date'],
                'created_by' => $user->id,
            ]);

            $this->syncLines($movement, $lines);

            return $movement->load(['lines.product', 'sourceStock', 'destinationStock']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>|null  $lines
     */
    public function updateDraft(StockMovement $movement, array $data, ?array $lines = null): StockMovement
    {
        $this->assertDraft($movement);

        return DB::transaction(function () use ($movement, $data, $lines) {
            if (isset($data['movement_type'])) {
                $type = StockMovementType::from($data['movement_type']);
                $this->assertStocksForType($type, [
                    'source_stock_id' => $data['source_stock_id'] ?? $movement->source_stock_id,
                    'destination_stock_id' => $data['destination_stock_id'] ?? $movement->destination_stock_id,
                ]);
                $data['movement_type'] = $type;
            }

            $movement->update($data);

            if ($lines !== null) {
                $movement->lines()->delete();
                $this->syncLines($movement, $lines);
            }

            return $movement->fresh(['lines.product', 'sourceStock', 'destinationStock']);
        });
    }

    public function validate(StockMovement $movement, User $user): StockMovement
    {
        $this->assertDraft($movement);

        if ($movement->lines()->count() === 0) {
            throw ValidationException::withMessages([
                'lines' => ['Le mouvement doit contenir au moins une ligne.'],
            ]);
        }

        return DB::transaction(function () use ($movement, $user) {
            $movement = StockMovement::query()
                ->whereKey($movement->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertDraft($movement);
            $movement->load('lines');

            foreach ($movement->lines as $line) {
                $this->applyLine($movement, $line, reverse: false);
            }

            $movement->update([
                'status' => StockMovementStatus::Validated,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);

            return $movement->fresh(['lines.product', 'sourceStock', 'destinationStock', 'validator']);
        });
    }

    public function cancel(StockMovement $movement, User $user, ?string $reason = null): StockMovement
    {
        if (! $movement->isValidated()) {
            throw ValidationException::withMessages([
                'status' => ['Seul un mouvement validé peut être annulé.'],
            ]);
        }

        return DB::transaction(function () use ($movement, $user, $reason) {
            $movement = StockMovement::query()
                ->whereKey($movement->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $movement->isValidated()) {
                throw ValidationException::withMessages([
                    'status' => ['Seul un mouvement validé peut être annulé.'],
                ]);
            }

            $movement->load('lines');

            foreach ($movement->lines as $line) {
                $this->applyLine($movement, $line, reverse: true);
            }

            $movement->update([
                'status' => StockMovementStatus::Cancelled,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $movement->fresh(['lines.product', 'sourceStock', 'destinationStock', 'canceller']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncLines(StockMovement $movement, array $lines): void
    {
        foreach ($lines as $line) {
            $movement->lines()->create([
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'] ?? null,
                'note' => $line['note'] ?? null,
            ]);
        }
    }

    private function applyLine(StockMovement $movement, StockMovementLine $line, bool $reverse): void
    {
        $type = $movement->movement_type;
        $quantity = (string) $line->quantity;

        $sourceBefore = null;
        $sourceAfter = null;
        $destinationBefore = null;
        $destinationAfter = null;

        if ($this->shouldDecreaseSource($type, $movement)) {
            $delta = $reverse ? $quantity : bcmul($quantity, '-1', 4);
            [$sourceBefore, $sourceAfter] = $this->adjustStockItem(
                $movement->source_stock_id,
                $line->product_id,
                $delta,
                allowNegative: $reverse,
            );
        }

        if ($this->shouldIncreaseDestination($type, $movement)) {
            $delta = $reverse ? bcmul($quantity, '-1', 4) : $quantity;
            [$destinationBefore, $destinationAfter] = $this->adjustStockItem(
                $movement->destination_stock_id,
                $line->product_id,
                $delta,
                allowNegative: $reverse,
            );
        }

        if ($type === StockMovementType::Adjustment) {
            if ($movement->source_stock_id && ! $movement->destination_stock_id) {
                $delta = $reverse ? $quantity : bcmul($quantity, '-1', 4);
                [$sourceBefore, $sourceAfter] = $this->adjustStockItem(
                    $movement->source_stock_id,
                    $line->product_id,
                    $delta,
                    allowNegative: $reverse,
                );
            } elseif ($movement->destination_stock_id && ! $movement->source_stock_id) {
                $delta = $reverse ? bcmul($quantity, '-1', 4) : $quantity;
                [$destinationBefore, $destinationAfter] = $this->adjustStockItem(
                    $movement->destination_stock_id,
                    $line->product_id,
                    $delta,
                    allowNegative: $reverse,
                );
            }
        }

        if (! $reverse) {
            $line->update([
                'source_quantity_before' => $sourceBefore,
                'source_quantity_after' => $sourceAfter,
                'destination_quantity_before' => $destinationBefore,
                'destination_quantity_after' => $destinationAfter,
            ]);
        }
    }

    private function shouldDecreaseSource(StockMovementType $type, StockMovement $movement): bool
    {
        if ($type === StockMovementType::Adjustment) {
            return false;
        }

        return in_array($type, [
            StockMovementType::Exit,
            StockMovementType::TransferOut,
            StockMovementType::Sale,
        ], true) && $movement->source_stock_id;
    }

    private function shouldIncreaseDestination(StockMovementType $type, StockMovement $movement): bool
    {
        if ($type === StockMovementType::Adjustment) {
            return false;
        }

        if ($type === StockMovementType::TransferOut) {
            return (bool) $movement->destination_stock_id;
        }

        return in_array($type, [
            StockMovementType::InitialEntry,
            StockMovementType::Entry,
            StockMovementType::TransferIn,
            StockMovementType::SaleReturn,
        ], true) && $movement->destination_stock_id;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function adjustStockItem(int $stockId, int $productId, string $delta, bool $allowNegative): array
    {
        $item = StockItem::query()
            ->where('stock_id', $stockId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $item) {
            $item = StockItem::query()->create([
                'stock_id' => $stockId,
                'product_id' => $productId,
                'quantity_on_hand' => '0.0000',
                'quantity_reserved' => '0.0000',
            ]);

            $item = StockItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
        }

        $before = (string) $item->quantity_on_hand;
        $after = bcadd($before, $delta, 4);

        if (! $allowNegative && bccomp($after, '0', 4) < 0) {
            throw ValidationException::withMessages([
                'lines' => ["Stock insuffisant pour le produit #{$productId} dans le stock #{$stockId}."],
            ]);
        }

        $item->quantity_on_hand = $after;
        $item->save();

        return [$before, $after];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertStocksForType(StockMovementType $type, array $data): void
    {
        $source = $data['source_stock_id'] ?? null;
        $destination = $data['destination_stock_id'] ?? null;

        $requiresSource = in_array($type, [
            StockMovementType::Exit,
            StockMovementType::TransferOut,
            StockMovementType::Sale,
        ], true);

        $requiresDestination = in_array($type, [
            StockMovementType::InitialEntry,
            StockMovementType::Entry,
            StockMovementType::TransferIn,
            StockMovementType::SaleReturn,
        ], true);

        if ($requiresSource && ! $source) {
            throw ValidationException::withMessages([
                'source_stock_id' => ['Le stock source est obligatoire pour ce type de mouvement.'],
            ]);
        }

        if ($requiresDestination && ! $destination) {
            throw ValidationException::withMessages([
                'destination_stock_id' => ['Le stock destination est obligatoire pour ce type de mouvement.'],
            ]);
        }

        if ($type === StockMovementType::TransferOut && $source && $destination && (int) $source === (int) $destination) {
            throw ValidationException::withMessages([
                'destination_stock_id' => ['Le stock destination doit être différent du stock source.'],
            ]);
        }

        if ($type === StockMovementType::Adjustment && ! $source && ! $destination) {
            throw ValidationException::withMessages([
                'source_stock_id' => ['Un stock source ou destination est obligatoire pour un ajustement.'],
            ]);
        }

        if ($type === StockMovementType::Adjustment && $source && $destination) {
            throw ValidationException::withMessages([
                'destination_stock_id' => ['Un ajustement ne peut cibler qu’un seul stock.'],
            ]);
        }
    }

    private function assertDraft(StockMovement $movement): void
    {
        if (! $movement->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Seul un brouillon peut être modifié ou validé.'],
            ]);
        }
    }
}
