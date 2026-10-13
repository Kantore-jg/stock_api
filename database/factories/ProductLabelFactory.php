<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProductLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductLabel>
 */
class ProductLabelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'default_tax_rate' => '18.0000',
            'tax_included_in_price' => false,
            'is_tax_exempt' => false,
            'is_active' => true,
        ];
    }

    public function exempt(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_tax_rate' => '0.0000',
            'is_tax_exempt' => true,
        ]);
    }
}
