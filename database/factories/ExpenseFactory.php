<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'reference' => 'DEP-'.fake()->unique()->numerify('######'),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(4, 1000, 100000),
            'currency_id' => Currency::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'expense_date' => now(),
            'status' => ExpenseStatus::Validated,
            'created_by' => User::factory(),
        ];
    }
}
