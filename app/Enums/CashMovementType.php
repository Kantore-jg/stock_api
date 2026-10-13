<?php

namespace App\Enums;

enum CashMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
