<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'action' => 'test.action',
            'auditable_type' => User::class,
            'auditable_id' => 1,
            'old_values' => null,
            'new_values' => ['ok' => true],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ];
    }
}
