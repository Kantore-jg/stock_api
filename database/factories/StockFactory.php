<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => strtoupper(fake()->unique()->bothify('STK-###')),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->address(),
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
