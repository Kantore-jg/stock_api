<?php

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashRegister>
 */
class CashRegisterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_id' => Stock::factory(),
            'name' => 'Caisse '.fake()->word(),
            'currency_id' => Currency::factory(),
            'is_active' => true,
        ];
    }
}
