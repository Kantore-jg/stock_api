<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductLabel;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => Category::factory(),
            'product_label_id' => ProductLabel::factory(),
            'unit_id' => Unit::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'barcode' => fake()->optional()->ean13(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'purchase_price' => fake()->randomFloat(4, 1000, 500000),
            'selling_price' => fake()->randomFloat(4, 1500, 800000),
            'currency_id' => Currency::factory(),
            'min_stock_level' => 0,
            'image_path' => null,
            'is_active' => true,
            'created_by' => null,
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
            'category_id' => Category::factory()->state(['company_id' => $company->id]),
            'product_label_id' => ProductLabel::factory()->state(['company_id' => $company->id]),
            'unit_id' => Unit::factory()->state(['company_id' => $company->id]),
            'currency_id' => $company->default_currency_id ?? Currency::factory(),
        ]);
    }
}
