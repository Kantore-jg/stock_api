<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockMovementLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovementLine>
 */
class StockMovementLineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_movement_id' => StockMovement::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->randomFloat(4, 1, 50),
            'unit_cost' => fake()->optional()->randomFloat(4, 100, 10000),
            'note' => null,
        ];
    }
}
