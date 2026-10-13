<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Currency;
use App\Models\NumberSequence;
use App\Models\ProductLabel;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class CompanyProvisioningService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{company: Company, admin: User}
     */
    public function provision(array $data): array
    {
        $currency = Currency::query()
            ->where('code', $data['currency_code'] ?? 'BIF')
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($data, $currency) {
            $company = Company::query()->create([
                'name' => $data['company_name'],
                'legal_name' => $data['legal_name'] ?? $data['company_name'],
                'tax_number' => $data['tax_number'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['company_phone'] ?? $data['phone'] ?? null,
                'email' => $data['company_email'] ?? $data['email'],
                'default_currency_id' => $currency->id,
                'is_active' => true,
            ]);

            app(RolesAndPermissionsSeeder::class)->seedRolesForCompany($company->id);
            app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

            $admin = User::query()->create([
                'company_id' => $company->id,
                'name' => $data['admin_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
                'is_super_admin' => false,
                'email_verified_at' => now(),
            ]);

            $admin->assignRole(RoleName::Admin->value);

            $stock = Stock::query()->create([
                'company_id' => $company->id,
                'code' => 'MAIN',
                'name' => $data['stock_name'] ?? 'Stock principal',
                'description' => 'Magasin principal',
                'address' => $data['address'] ?? null,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            $stock->users()->attach($admin->id, [
                'assigned_by' => $admin->id,
                'can_view' => true,
                'can_manage_stock' => true,
                'can_sell' => true,
                'is_default' => true,
                'assigned_at' => now(),
            ]);

            CashRegister::query()->create([
                'company_id' => $company->id,
                'stock_id' => $stock->id,
                'name' => 'Caisse principale',
                'currency_id' => $currency->id,
                'is_active' => true,
            ]);

            CompanySetting::query()->create([
                'company_id' => $company->id,
                'setting_key' => 'invoice_footer',
                'setting_value' => 'Merci pour votre achat.',
            ]);

            foreach (
                [
                    ['document_type' => 'sale', 'prefix' => 'V-'],
                    ['document_type' => 'entry', 'prefix' => 'ENT-'],
                    ['document_type' => 'exit', 'prefix' => 'SOR-'],
                    ['document_type' => 'transfer', 'prefix' => 'TRF-'],
                    ['document_type' => 'invoice', 'prefix' => 'INV-'],
                ] as $sequence
            ) {
                NumberSequence::query()->create([
                    'company_id' => $company->id,
                    'stock_id' => null,
                    'document_type' => $sequence['document_type'],
                    'prefix' => $sequence['prefix'],
                    'next_number' => 1,
                    'padding' => 6,
                ]);
            }

            Category::query()->create([
                'company_id' => $company->id,
                'name' => 'Général',
                'description' => 'Catégorie par défaut',
                'is_active' => true,
            ]);

            ProductLabel::query()->create([
                'company_id' => $company->id,
                'name' => 'TVA 18%',
                'default_tax_rate' => '18.0000',
                'tax_included_in_price' => false,
                'is_tax_exempt' => false,
                'is_active' => true,
            ]);

            ProductLabel::query()->create([
                'company_id' => $company->id,
                'name' => 'Exonéré',
                'default_tax_rate' => '0.0000',
                'tax_included_in_price' => false,
                'is_tax_exempt' => true,
                'is_active' => true,
            ]);

            Unit::query()->create([
                'company_id' => $company->id,
                'name' => 'Pièce',
                'abbreviation' => 'pcs',
                'allows_decimal' => false,
                'is_active' => true,
            ]);

            return [
                'company' => $company->fresh('defaultCurrency'),
                'admin' => $admin->fresh(),
            ];
        });
    }
}
