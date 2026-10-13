<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class StockAssignmentTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_admin_can_create_stock_and_is_auto_assigned(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('phpunit')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/stocks', [
                'code' => 'DEPOT-1',
                'name' => 'Dépôt principal',
                'address' => 'Bujumbura',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'DEPOT-1')
            ->assertJsonPath('data.company_id', $user->company_id);

        $stockId = $response->json('data.id');

        $this->assertDatabaseHas('stock_user', [
            'stock_id' => $stockId,
            'user_id' => $user->id,
            'can_view' => 1,
            'can_manage_stock' => 1,
            'can_sell' => 1,
        ]);
    }

    public function test_admin_can_assign_user_to_stock(): void
    {
        $company = $this->createCompany();
        $admin = $this->createAdminUser($company);
        $employee = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'employee@example.test',
        ]);

        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'created_by' => $admin->id,
        ]);

        $token = $admin->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/stocks/{$stock->id}/users", [
                'user_id' => $employee->id,
                'can_view' => true,
                'can_manage_stock' => true,
                'can_sell' => false,
                'is_default' => true,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('stock_user', [
            'stock_id' => $stock->id,
            'user_id' => $employee->id,
            'can_manage_stock' => 1,
            'can_sell' => 0,
            'is_default' => 1,
        ]);
    }
}
