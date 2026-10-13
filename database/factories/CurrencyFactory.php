<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->currencyCode(),
            'name' => fake()->word(),
            'symbol' => fake()->randomElement(['$', '€', 'FBu']),
            'decimal_places' => 2,
            'is_active' => true,
        ];
    }
}
