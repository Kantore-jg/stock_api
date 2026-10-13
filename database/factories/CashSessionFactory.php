<?php

namespace Database\Factories;

use App\Enums\CashSessionStatus;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashSession>
 */
class CashSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'user_id' => User::factory(),
            'opening_amount' => 0,
            'status' => CashSessionStatus::Open,
            'opened_at' => now(),
        ];
    }
}
