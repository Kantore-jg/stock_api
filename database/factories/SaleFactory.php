<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_id' => Stock::factory(),
            'customer_id' => null,
            'invoice_number' => null,
            'status' => SaleStatus::Draft,
            'sale_date' => now(),
            'currency_id' => Currency::factory(),
            'exchange_rate' => 1,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'created_by' => User::factory(),
        ];
    }
}
