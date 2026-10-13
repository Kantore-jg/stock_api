<?php

namespace Database\Factories;

use App\Enums\StockMovementStatus;
use App\Enums\StockMovementType;
use App\Models\Company;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'reference' => 'ENT-'.fake()->unique()->numerify('######'),
            'movement_type' => StockMovementType::Entry,
            'status' => StockMovementStatus::Draft,
            'source_stock_id' => null,
            'destination_stock_id' => Stock::factory(),
            'reason' => fake()->optional()->sentence(),
            'document_date' => now(),
            'created_by' => User::factory(),
        ];
    }
}
