<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isOpen(): bool
    {
        return in_array($this, [
            self::Validated,
            self::PartiallyPaid,
            self::Paid,
        ], true);
    }
}
