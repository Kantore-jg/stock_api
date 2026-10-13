<?php

namespace App\Enums;

enum StockMovementType: string
{
    case InitialEntry = 'initial_entry';
    case Entry = 'entry';
    case Exit = 'exit';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Adjustment = 'adjustment';
    case Sale = 'sale';
    case SaleReturn = 'sale_return';

    public function affectsDestination(): bool
    {
        return in_array($this, [
            self::InitialEntry,
            self::Entry,
            self::TransferIn,
            self::TransferOut,
            self::SaleReturn,
            self::Adjustment,
        ], true);
    }

    public function affectsSource(): bool
    {
        return in_array($this, [
            self::Exit,
            self::TransferOut,
            self::Sale,
            self::Adjustment,
        ], true);
    }

    public function documentType(): string
    {
        return match ($this) {
            self::InitialEntry, self::Entry, self::TransferIn, self::SaleReturn => 'entry',
            self::Exit, self::Sale => 'exit',
            self::TransferOut => 'transfer',
            self::Adjustment => 'adjustment',
        };
    }

    public function defaultPrefix(): string
    {
        return match ($this->documentType()) {
            'entry' => 'ENT-',
            'exit' => 'SOR-',
            'transfer' => 'TRF-',
            'adjustment' => 'ADJ-',
            default => 'MVT-',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
