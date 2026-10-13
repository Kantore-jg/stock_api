<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company().' SARL',
            'tax_number' => fake()->numerify('TIN-########'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'default_currency_id' => Currency::factory(),
            'is_active' => true,
        ];
    }
}
