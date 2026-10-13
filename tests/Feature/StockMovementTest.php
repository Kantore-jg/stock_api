<?php

namespace Tests\Feature;

use App\Enums\StockMovementStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLabel;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_entry_validation_increases_stock_quantity(): void
    {
        [$user, $stock, $product] = $this->setupCompanyCatalog();
        $token = $user->createToken('phpunit')->plainTextToken;

        $create = $this->withToken($token)->postJson('/api/stock-movements', [
            'movement_type' => StockMovementType::Entry->value,
            'destination_stock_id' => $stock->id,
            'document_date' => now()->toISOString(),
            'reason' => 'Réapprovisionnement',
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 1000],
            ],
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.status', StockMovementStatus::Draft->value)
            ->assertJsonPath('data.movement_type', StockMovementType::Entry->value);

        $movementId = $create->json('data.id');

        $this->withToken($token)
            ->postJson("/api/stock-movements/{$movementId}/validate")
            ->assertOk()
            ->assertJsonPath('data.status', StockMovementStatus::Validated->value);

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '10.0000',
        ]);
    }

    public function test_exit_fails_when_stock_is_insufficient(): void
    {
        [$user, $stock, $product] = $this->setupCompanyCatalog();
        $token = $user->createToken('phpunit')->plainTextToken;

        StockItem::factory()->create([
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '3.0000',
        ]);

        $create = $this->withToken($token)->postJson('/api/stock-movements', [
            'movement_type' => StockMovementType::Exit->value,
            'source_stock_id' => $stock->id,
            'document_date' => now()->toISOString(),
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $create->assertCreated();
        $movementId = $create->json('data.id');

        $this->withToken($token)
            ->postJson("/api/stock-movements/{$movementId}/validate")
            ->assertStatus(422);
    }

    public function test_transfer_moves_quantity_between_stocks(): void
    {
        [$user, $source, $product] = $this->setupCompanyCatalog();
        $destination = Stock::factory()->create([
            'company_id' => $user->company_id,
            'code' => 'DEPOT-2',
            'created_by' => $user->id,
        ]);

        StockItem::factory()->create([
            'stock_id' => $source->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '20.0000',
        ]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $create = $this->withToken($token)->postJson('/api/stock-movements', [
            'movement_type' => StockMovementType::TransferOut->value,
            'source_stock_id' => $source->id,
            'destination_stock_id' => $destination->id,
            'document_date' => now()->toISOString(),
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 7],
            ],
        ])->assertCreated();

        $this->withToken($token)
            ->postJson('/api/stock-movements/'.$create->json('data.id').'/validate')
            ->assertOk();

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $source->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '13.0000',
        ]);

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $destination->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '7.0000',
        ]);
    }

    public function test_cancel_reverses_validated_entry(): void
    {
        [$user, $stock, $product] = $this->setupCompanyCatalog();
        $token = $user->createToken('phpunit')->plainTextToken;

        $create = $this->withToken($token)->postJson('/api/stock-movements', [
            'movement_type' => StockMovementType::Entry->value,
            'destination_stock_id' => $stock->id,
            'document_date' => now()->toISOString(),
            'lines' => [
                ['product_id' => $product->id, 'quantity' => 15],
            ],
        ])->assertCreated();

        $movementId = $create->json('data.id');

        $this->withToken($token)
            ->postJson("/api/stock-movements/{$movementId}/validate")
            ->assertOk();

        $this->withToken($token)
            ->postJson("/api/stock-movements/{$movementId}/cancel", [
                'cancellation_reason' => 'Erreur de saisie',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', StockMovementStatus::Cancelled->value);

        $this->assertDatabaseHas('stock_items', [
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '0.0000',
        ]);
    }

    public function test_stock_items_endpoint_lists_quantities(): void
    {
        [$user, $stock, $product] = $this->setupCompanyCatalog();
        StockItem::factory()->create([
            'stock_id' => $stock->id,
            'product_id' => $product->id,
            'quantity_on_hand' => '8.0000',
        ]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/stocks/{$stock->id}/items")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity_on_hand', '8.0000');
    }

    /**
     * @return array{0: User, 1: Stock, 2: Product}
     */
    protected function setupCompanyCatalog(): array
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $stock = Stock::factory()->create([
            'company_id' => $company->id,
            'code' => 'MAIN',
            'created_by' => $user->id,
        ]);

        $category = Category::factory()->create(['company_id' => $company->id]);
        $label = ProductLabel::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'product_label_id' => $label->id,
            'unit_id' => $unit->id,
            'currency_id' => $company->default_currency_id,
            'sku' => 'SKU-MOVE-1',
            'min_stock_level' => 2,
        ]);

        return [$user, $stock, $product];
    }
}
