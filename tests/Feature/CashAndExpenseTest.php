<?php

namespace Tests\Feature;

use App\Enums\CashSessionStatus;
use App\Enums\ExpenseStatus;
use App\Models\CashRegister;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class CashAndExpenseTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_cash_session_open_movement_and_close(): void
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
        ]);
        $register = CashRegister::factory()->create([
            'company_id' => $company->id,
            'stock_id' => $stock->id,
            'currency_id' => $company->default_currency_id,
            'name' => 'Caisse principale',
        ]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $open = $this->withToken($token)->postJson("/api/cash-registers/{$register->id}/sessions", [
            'opening_amount' => 100000,
        ])->assertCreated();

        $sessionId = $open->json('data.id');
        $this->assertEquals(CashSessionStatus::Open->value, $open->json('data.status'));

        $this->withToken($token)->postJson("/api/cash-sessions/{$sessionId}/movements", [
            'movement_type' => 'in',
            'amount' => 50000,
            'description' => 'Encaissement manuel',
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/cash-sessions/{$sessionId}/movements", [
            'movement_type' => 'out',
            'amount' => 20000,
            'description' => 'Retrait',
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/cash-sessions/{$sessionId}/close", [
            'counted_amount' => 130000,
            'closing_notes' => 'OK',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', CashSessionStatus::Closed->value)
            ->assertJsonPath('data.expected_amount', '130000.0000')
            ->assertJsonPath('data.difference_amount', '0.0000');

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'cash_session.closed',
        ]);
    }

    public function test_expense_with_cash_session_creates_out_movement(): void
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
        ]);
        $register = CashRegister::factory()->create([
            'company_id' => $company->id,
            'stock_id' => $stock->id,
            'currency_id' => $company->default_currency_id,
        ]);
        $category = ExpenseCategory::factory()->create([
            'company_id' => $company->id,
            'name' => 'Transport',
        ]);
        $method = PaymentMethod::query()->create([
            'code' => 'cash',
            'name' => 'Espèces',
            'is_active' => true,
        ]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $sessionId = $this->withToken($token)->postJson("/api/cash-registers/{$register->id}/sessions", [
            'opening_amount' => 200000,
        ])->json('data.id');

        $this->withToken($token)->postJson('/api/expenses', [
            'expense_category_id' => $category->id,
            'cash_session_id' => $sessionId,
            'description' => 'Carburant moto',
            'amount' => 25000,
            'currency_id' => $company->default_currency_id,
            'payment_method_id' => $method->id,
            'expense_date' => now()->toISOString(),
            'status' => ExpenseStatus::Validated->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', ExpenseStatus::Validated->value);

        $this->assertDatabaseHas('cash_movements', [
            'cash_session_id' => $sessionId,
            'movement_type' => 'out',
            'amount' => '25000.0000',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'expense.created',
        ]);
    }

    public function test_cannot_open_two_sessions_on_same_register(): void
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
        ]);
        $register = CashRegister::factory()->create([
            'company_id' => $company->id,
            'stock_id' => $stock->id,
            'currency_id' => $company->default_currency_id,
        ]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)->postJson("/api/cash-registers/{$register->id}/sessions", [
            'opening_amount' => 0,
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/cash-registers/{$register->id}/sessions", [
            'opening_amount' => 0,
        ])->assertStatus(422);
    }
}
