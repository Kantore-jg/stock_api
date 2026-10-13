<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\NumberSequence;
use App\Models\Product;
use App\Models\ProductLabel;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DemoCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bif = Currency::query()->where('code', 'BIF')->firstOrFail();

        $company = Company::query()->updateOrCreate(
            ['email' => 'codewithkantox@gmail.com'],
            [
                'name' => 'Kanto Stock Demo',
                'legal_name' => 'Kanto Stock Demo SARL',
                'tax_number' => 'TIN-DEMO-001',
                'address' => 'Bujumbura, Burundi',
                'phone' => '+257 00 00 00 00',
                'default_currency_id' => $bif->id,
                'is_active' => true,
            ],
        );

        app(RolesAndPermissionsSeeder::class)->seedRolesForCompany($company->id);

        app()[PermissionRegistrar::class]->setPermissionsTeamId($company->id);

        $admin = User::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'email' => 'codewithkantox@gmail.com',
            ],
            [
                'name' => 'Jean Gildas',
                'phone' => '+257 79 00 00 00',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $admin->assignRole(RoleName::Admin->value);

        $stock = Stock::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'MAIN',
            ],
            [
                'name' => 'Stock principal',
                'description' => 'Magasin principal',
                'address' => 'Bujumbura',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        );

        $depot = Stock::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'DEPOT',
            ],
            [
                'name' => 'Dépôt secondaire',
                'description' => 'Entrepôt / dépôt',
                'address' => 'Bujumbura',
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        );

        $stockAssignment = [
            'assigned_by' => $admin->id,
            'can_view' => true,
            'can_manage_stock' => true,
            'can_sell' => true,
            'assigned_at' => now(),
        ];

        $stock->users()->syncWithoutDetaching([
            $admin->id => [...$stockAssignment, 'is_default' => true],
        ]);

        $depot->users()->syncWithoutDetaching([
            $admin->id => [...$stockAssignment, 'is_default' => false],
        ]);

        CompanySetting::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'setting_key' => 'invoice_footer',
            ],
            ['setting_value' => 'Merci pour votre achat.'],
        );

        foreach (
            [
                ['document_type' => 'sale', 'prefix' => 'V-'],
                ['document_type' => 'entry', 'prefix' => 'ENT-'],
                ['document_type' => 'invoice', 'prefix' => 'INV-'],
            ] as $sequence
        ) {
            NumberSequence::query()->withoutGlobalScopes()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'stock_id' => null,
                    'document_type' => $sequence['document_type'],
                ],
                [
                    'prefix' => $sequence['prefix'],
                    'next_number' => 1,
                    'padding' => 6,
                ],
            );
        }

        $category = Category::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Informatique',
            ],
            [
                'description' => 'Matériel informatique',
                'is_active' => true,
            ],
        );

        $label = ProductLabel::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'TVA 18%',
            ],
            [
                'default_tax_rate' => '18.0000',
                'tax_included_in_price' => false,
                'is_tax_exempt' => false,
                'is_active' => true,
            ],
        );

        ProductLabel::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Exonéré',
            ],
            [
                'default_tax_rate' => '0.0000',
                'tax_included_in_price' => false,
                'is_tax_exempt' => true,
                'is_active' => true,
            ],
        );

        $unit = Unit::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'abbreviation' => 'pcs',
            ],
            [
                'name' => 'Pièce',
                'allows_decimal' => false,
                'is_active' => true,
            ],
        );

        Product::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'sku' => 'PC-HP-001',
            ],
            [
                'category_id' => $category->id,
                'product_label_id' => $label->id,
                'unit_id' => $unit->id,
                'barcode' => null,
                'name' => 'Ordinateur HP',
                'description' => 'Ordinateur portable HP',
                'purchase_price' => '800000.0000',
                'selling_price' => '1000000.0000',
                'currency_id' => $bif->id,
                'min_stock_level' => 2,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        );
    }
}
