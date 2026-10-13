<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\NumberSequenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'stock_id',
    'document_type',
    'prefix',
    'next_number',
    'padding',
])]
class NumberSequence extends Model
{
    /** @use HasFactory<NumberSequenceFactory> */
    use BelongsToCompany, HasFactory;

    public const CREATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
            'padding' => 'integer',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
