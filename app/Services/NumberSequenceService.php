<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberSequenceService
{
    public function next(
        int $companyId,
        string $documentType,
        string $defaultPrefix,
        ?int $stockId = null,
        int $padding = 6,
    ): string {
        return DB::transaction(function () use ($companyId, $documentType, $defaultPrefix, $stockId, $padding) {
            $query = NumberSequence::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('document_type', $documentType);

            if ($stockId === null) {
                $query->whereNull('stock_id');
            } else {
                $query->where('stock_id', $stockId);
            }

            $sequence = $query->lockForUpdate()->first();

            if (! $sequence) {
                $sequence = NumberSequence::query()->withoutGlobalScopes()->create([
                    'company_id' => $companyId,
                    'stock_id' => $stockId,
                    'document_type' => $documentType,
                    'prefix' => $defaultPrefix,
                    'next_number' => 1,
                    'padding' => $padding,
                ]);
                $sequence = NumberSequence::query()
                    ->withoutGlobalScopes()
                    ->whereKey($sequence->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $number = $sequence->next_number;
            $sequence->next_number = $number + 1;
            $sequence->save();

            return $sequence->prefix.str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT);
        });
    }
}
