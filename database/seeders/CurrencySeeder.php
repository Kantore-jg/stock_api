<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'BIF', 'name' => 'Franc burundais', 'symbol' => 'FBu', 'decimal_places' => 0],
            ['code' => 'USD', 'name' => 'Dollar américain', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['code' => $currency['code']],
                [...$currency, 'is_active' => true],
            );
        }
    }
}
