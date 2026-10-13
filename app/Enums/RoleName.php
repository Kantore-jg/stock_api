<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Cashier = 'cashier';
    case StockKeeper = 'stock_keeper';
}
