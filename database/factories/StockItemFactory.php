<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_id' => Stock::factory(),
            'product_id' => Product::factory(),
            'quantity_on_hand' => fake()->randomFloat(4, 0, 100),
            'quantity_reserved' => 0,
        ];
    }
}
