<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 10);
        $unitPrice = fake()->randomFloat(4, 1000, 50000);
        $lineSubtotal = $quantity * $unitPrice;

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'product_name_snapshot' => fake()->words(3, true),
            'product_sku_snapshot' => strtoupper(fake()->bothify('SKU-###')),
            'label_name_snapshot' => 'TVA 18%',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => 0,
            'tax_rate_snapshot' => 18,
            'tax_amount' => $lineSubtotal * 0.18,
            'line_subtotal' => $lineSubtotal,
            'line_total' => $lineSubtotal * 1.18,
        ];
    }
}
