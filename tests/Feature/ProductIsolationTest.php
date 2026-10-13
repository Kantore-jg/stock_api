<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLabel;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class ProductIsolationTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_user_only_sees_products_from_their_company(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();
        $userA = $this->createAdminUser($companyA);
        $this->createAdminUser($companyB);

        $productA = $this->makeProductForCompany($companyA, 'SKU-A');
        $this->makeProductForCompany($companyB, 'SKU-B');

        $token = $userA->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $productA->id)
            ->assertJsonPath('data.0.sku', 'SKU-A');
    }

    public function test_user_cannot_view_product_from_another_company(): void
    {
        $companyA = $this->createCompany();
        $companyB = $this->createCompany();
        $userA = $this->createAdminUser($companyA);
        $productB = $this->makeProductForCompany($companyB, 'SKU-B');

        $token = $userA->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/products/'.$productB->id)
            ->assertNotFound();
    }

    public function test_user_can_create_product_in_their_company(): void
    {
        $company = $this->createCompany();
        $user = $this->createAdminUser($company);
        $currency = $company->defaultCurrency;
        $category = Category::factory()->create(['company_id' => $company->id]);
        $label = ProductLabel::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create(['company_id' => $company->id]);

        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/products', [
                'category_id' => $category->id,
                'product_label_id' => $label->id,
                'unit_id' => $unit->id,
                'sku' => 'NEW-001',
                'name' => 'Nouveau produit',
                'purchase_price' => 1000,
                'selling_price' => 1500,
                'currency_id' => $currency->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.sku', 'NEW-001')
            ->assertJsonPath('data.company_id', $company->id);

        $this->assertDatabaseHas('products', [
            'sku' => 'NEW-001',
            'company_id' => $company->id,
        ]);
    }

    protected function makeProductForCompany($company, string $sku): Product
    {
        $category = Category::factory()->create(['company_id' => $company->id]);
        $label = ProductLabel::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create(['company_id' => $company->id]);

        return Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'product_label_id' => $label->id,
            'unit_id' => $unit->id,
            'currency_id' => $company->default_currency_id,
            'sku' => $sku,
        ]);
    }
}
