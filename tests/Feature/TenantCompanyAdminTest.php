<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantCompanyAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_superadmin_can_create_a_tenant_company(): void
    {
        $super = User::factory()->create([
            'company_id' => null,
            'email' => 'super@platform.test',
            'is_super_admin' => true,
            'password' => 'password',
        ]);

        $token = $super->createToken('phpunit')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/admin/companies', [
            'company_name' => 'Client Alpha',
            'admin_name' => 'Admin Alpha',
            'email' => 'admin@client-alpha.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'currency_code' => 'BIF',
        ]);

        $response->assertCreated()
            ->assertJsonPath('company.name', 'Client Alpha')
            ->assertJsonPath('admin.email', 'admin@client-alpha.test')
            ->assertJsonPath('admin.company_id', $response->json('company.id'));

        $this->assertDatabaseHas('companies', ['name' => 'Client Alpha']);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@client-alpha.test',
            'is_super_admin' => 0,
        ]);
        $this->assertDatabaseHas('stocks', ['code' => 'MAIN']);
    }

    public function test_regular_user_cannot_create_tenant_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'is_super_admin' => false,
        ]);
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)->postJson('/api/admin/companies', [
            'company_name' => 'Hack Co',
            'admin_name' => 'Hacker',
            'email' => 'hack@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertForbidden();
    }
}
