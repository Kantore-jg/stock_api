<?php

namespace Tests\Concerns;

use App\Enums\RoleName;
use App\Models\Company;
use App\Models\Currency;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\PermissionRegistrar;

trait CreatesCompanyUsers
{
    protected function createCurrency(string $code = 'BIF'): Currency
    {
        return Currency::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $code,
                'symbol' => $code,
                'decimal_places' => $code === 'BIF' ? 0 : 2,
                'is_active' => true,
            ],
        );
    }

    protected function createCompany(?Currency $currency = null): Company
    {
        $currency ??= $this->createCurrency();

        return Company::factory()->create([
            'default_currency_id' => $currency->id,
        ]);
    }

    protected function createAdminUser(?Company $company = null): User
    {
        $company ??= $this->createCompany();

        app(RolesAndPermissionsSeeder::class)->run();
        app(RolesAndPermissionsSeeder::class)->seedRolesForCompany($company->id);
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'admin-'.$company->id.'@example.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        $user->assignRole(RoleName::Admin->value);

        return $user;
    }
}
