<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sale_id' => Sale::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'currency_id' => Currency::factory(),
            'amount' => fake()->randomFloat(4, 1000, 100000),
            'exchange_rate' => 1,
            'reference' => null,
            'payment_date' => now(),
            'status' => PaymentStatus::Confirmed,
            'received_by' => User::factory(),
        ];
    }
}
