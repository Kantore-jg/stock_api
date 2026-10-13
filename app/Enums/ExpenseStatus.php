<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
