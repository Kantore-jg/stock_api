<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductLabel;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_sale_validation_creates_invoice_and_decreases_stock(): void
    {
        [$user, $stock, $product] = $this->setupSaleContext(qty: '10.0000');
        $token = $user->createToken('phpunit')->plainTextToken;

        $create = $this->withToken($token)->postJson('/api/sales', [
            'stock_id' => $stock->id,
            'sale_date' => now()->toISOString(),
            'currency_id' => $user->company->default_currency_id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 1000000,
                ],
            ],
        ])->assertCreated();

        $saleId = $create->json('data.id');

        $this->assertEquals(SaleStatus::Draft->value, $create->json('data.status'));
        $this->assertEquals('2000000.0000', $create->json('data.subtotal'));
        $this->assertEquals('360000.0000', $create->json('data.tax_amount'));
        $this->assertEquals('2360000.0000', $create->json('data.total_amount'));

        $this->withToken($token)
            ->postJson("/api/sales/{$saleId}/validate")
            ->assertOk()
            ->assertJsonPath('data.status', SaleStatus::Validated->value)
            ->assertJsonPath('data.invoice.status', 'issued');

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '8.0000',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => StockMovementType::Sale->value,
            'status' => 'validated',
            'source_stock_id' => $stock->id,
        ]);
    }

    public function test_partial_and_full_payments_update_sale_status(): void
    {
        [$user, $stock, $product] = $this->setupSaleContext(qty: '5.0000');
        $method = PaymentMethod::query()->create([
            'code' => 'cash',
            'name' => 'Espèces',
            'is_active' => true,
        ]);
        $token = $user->createToken('phpunit')->plainTextToken;

        $saleId = $this->withToken($token)->postJson('/api/sales', [
            'stock_id' => $stock->id,
            'sale_date' => now()->toISOString(),
            'currency_id' => $user->company->default_currency_id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100000],
            ],
        ])->json('data.id');

        $this->withToken($token)->postJson("/api/sales/{$saleId}/validate")->assertOk();

        $this->withToken($token)->postJson("/api/sales/{$saleId}/payments", [
            'payment_method_id' => $method->id,
            'amount' => 50000,
            'payment_date' => now()->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('sale.status', SaleStatus::PartiallyPaid->value);

        $this->withToken($token)->postJson("/api/sales/{$saleId}/payments", [
            'payment_method_id' => $method->id,
            'amount' => 68000,
            'payment_date' => now()->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('sale.status', SaleStatus::Paid->value)
            ->assertJsonPath('sale.balance_due', '0.0000');
    }

    public function test_cancel_sale_restores_stock_and_cancels_invoice(): void
    {
        [$user, $stock, $product] = $this->setupSaleContext(qty: '10.0000');
        $token = $user->createToken('phpunit')->plainTextToken;

        $saleId = $this->withToken($token)->postJson('/api/sales', [
            'stock_id' => $stock->id,
            'sale_date' => now()->toISOString(),
            'currency_id' => $user->company->default_currency_id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1000],
            ],
        ])->json('data.id');

        $this->withToken($token)->postJson("/api/sales/{$saleId}/validate")->assertOk();

        $this->withToken($token)->postJson("/api/sales/{$saleId}/cancel", [
            'cancellation_reason' => 'Erreur client',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', SaleStatus::Cancelled->value)
            ->assertJsonPath('data.invoice.status', 'cancelled');

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '10.0000',
        ]);
    }

    public function test_customer_crud_works(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)->postJson('/api/customers', [
            'code' => 'CLI-01',
            'name' => 'Boutique Soleil',
            'phone' => '+25770000000',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Boutique Soleil');

        $this->assertDatabaseHas('customers', [
            'company_id' => $user->company_id,
            'code' => 'CLI-01',
        ]);
    }

    /**
     * @return array{0: User, 1: Stock, 2: Product}
     */
    protected function setupSaleContext(string $qty): array
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'code' => 'MAIN',
            'created_by' => $user->id,
        ]);

        $category = Category::factory()->create(['company_id' => $company->id]);
        $label = ProductLabel::factory()->create([
            'company_id' => $company->id,
            'default_tax_rate' => '18.0000',
            'is_tax_exempt' => false,
        ]);
        $unit = Unit::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'product_label_id' => $label->id,
            'unit_id' => $unit->id,
            'currency_id' => $company->default_currency_id,
            'sku' => 'SKU-SALE-1',
            'selling_price' => '1000000.0000',
        ]);

        StockItem::factory()->create([
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => $qty,
        ]);

        Customer::factory()->create(['company_id' => $company->id]);

        return [$user, $stock, $product];
    }
}
