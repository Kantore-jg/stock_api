<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Pièce', 'Kilogramme', 'Litre', 'Carton']),
            'abbreviation' => fake()->randomElement(['pcs', 'kg', 'L', 'ctn']),
            'allows_decimal' => false,
            'is_active' => true,
        ];
    }
}
