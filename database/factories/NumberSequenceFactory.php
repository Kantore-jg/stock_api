<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\NumberSequence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NumberSequence>
 */
class NumberSequenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_id' => null,
            'document_type' => fake()->randomElement(['sale', 'entry', 'invoice']),
            'prefix' => fake()->randomElement(['V-', 'ENT-', 'INV-']),
            'next_number' => 1,
            'padding' => 6,
        ];
    }
}
