<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sale_id' => Sale::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'invoice_date' => now(),
            'status' => InvoiceStatus::Issued,
            'customer_name_snapshot' => fake()->name(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 180,
            'total_amount' => 1180,
            'currency_id' => Currency::factory(),
            'issued_by' => User::factory(),
        ];
    }
}
