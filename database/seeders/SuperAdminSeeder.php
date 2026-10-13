<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@kanto.com'],
            [
                'company_id' => null,
                'name' => 'Super Admin',
                'phone' => null,
                'password' => 'password',
                'is_active' => true,
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
