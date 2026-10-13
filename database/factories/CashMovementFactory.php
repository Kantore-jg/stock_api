<?php

namespace Database\Factories;

use App\Enums\CashMovementType;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashMovement>
 */
class CashMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cash_session_id' => CashSession::factory(),
            'movement_type' => CashMovementType::In,
            'amount' => fake()->randomFloat(4, 1000, 50000),
            'description' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
