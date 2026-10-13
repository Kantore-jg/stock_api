<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'cash', 'name' => 'Espèces'],
            ['code' => 'bank', 'name' => 'Banque'],
            ['code' => 'credit', 'name' => 'À crédit'],
            ['code' => 'other', 'name' => 'Autre'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [...$method, 'is_active' => true],
            );
        }
    }
}
