<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionName::values() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $this->seedRolesForCompany(null);
    }

    public function seedRolesForCompany(?int $companyId): void
    {
        app()[PermissionRegistrar::class]->setPermissionsTeamId($companyId);

        $admin = Role::findOrCreate(RoleName::Admin->value, 'web');
        $admin->syncPermissions(PermissionName::values());

        $manager = Role::findOrCreate(RoleName::Manager->value, 'web');
        $manager->syncPermissions([
            PermissionName::CompanyView->value,
            PermissionName::CompanyUpdate->value,
            PermissionName::StocksView->value,
            PermissionName::StocksCreate->value,
            PermissionName::StocksUpdate->value,
            PermissionName::StocksAssignUsers->value,
            PermissionName::CurrenciesView->value,
            PermissionName::SettingsView->value,
            PermissionName::SettingsUpdate->value,
            PermissionName::CategoriesView->value,
            PermissionName::CategoriesCreate->value,
            PermissionName::CategoriesUpdate->value,
            PermissionName::ProductLabelsView->value,
            PermissionName::ProductLabelsCreate->value,
            PermissionName::ProductLabelsUpdate->value,
            PermissionName::UnitsView->value,
            PermissionName::UnitsCreate->value,
            PermissionName::UnitsUpdate->value,
            PermissionName::ProductsView->value,
            PermissionName::ProductsCreate->value,
            PermissionName::ProductsUpdate->value,
            PermissionName::StockItemsView->value,
            PermissionName::StockMovementsView->value,
            PermissionName::StockMovementsCreate->value,
            PermissionName::StockMovementsUpdate->value,
            PermissionName::StockMovementsValidate->value,
            PermissionName::StockMovementsCancel->value,
            PermissionName::CustomersView->value,
            PermissionName::CustomersCreate->value,
            PermissionName::CustomersUpdate->value,
            PermissionName::SalesView->value,
            PermissionName::SalesCreate->value,
            PermissionName::SalesUpdate->value,
            PermissionName::SalesValidate->value,
            PermissionName::SalesCancel->value,
            PermissionName::SalesPay->value,
            PermissionName::InvoicesView->value,
            PermissionName::PaymentsView->value,
            PermissionName::PaymentMethodsView->value,
            PermissionName::CashRegistersView->value,
            PermissionName::CashRegistersCreate->value,
            PermissionName::CashRegistersUpdate->value,
            PermissionName::CashSessionsOpen->value,
            PermissionName::CashSessionsClose->value,
            PermissionName::CashSessionsView->value,
            PermissionName::CashMovementsCreate->value,
            PermissionName::ExpenseCategoriesView->value,
            PermissionName::ExpenseCategoriesManage->value,
            PermissionName::ExpensesView->value,
            PermissionName::ExpensesCreate->value,
            PermissionName::ExpensesValidate->value,
            PermissionName::ExpensesCancel->value,
            PermissionName::AuditLogsView->value,
        ]);

        $cashier = Role::findOrCreate(RoleName::Cashier->value, 'web');
        $cashier->syncPermissions([
            PermissionName::CompanyView->value,
            PermissionName::StocksView->value,
            PermissionName::CurrenciesView->value,
            PermissionName::CategoriesView->value,
            PermissionName::ProductLabelsView->value,
            PermissionName::UnitsView->value,
            PermissionName::ProductsView->value,
            PermissionName::StockItemsView->value,
            PermissionName::StockMovementsView->value,
            PermissionName::CustomersView->value,
            PermissionName::CustomersCreate->value,
            PermissionName::CustomersUpdate->value,
            PermissionName::SalesView->value,
            PermissionName::SalesCreate->value,
            PermissionName::SalesUpdate->value,
            PermissionName::SalesValidate->value,
            PermissionName::SalesPay->value,
            PermissionName::InvoicesView->value,
            PermissionName::PaymentsView->value,
            PermissionName::PaymentMethodsView->value,
            PermissionName::CashRegistersView->value,
            PermissionName::CashSessionsOpen->value,
            PermissionName::CashSessionsClose->value,
            PermissionName::CashSessionsView->value,
            PermissionName::CashMovementsCreate->value,
            PermissionName::ExpenseCategoriesView->value,
            PermissionName::ExpensesView->value,
            PermissionName::ExpensesCreate->value,
        ]);

        $stockKeeper = Role::findOrCreate(RoleName::StockKeeper->value, 'web');
        $stockKeeper->syncPermissions([
            PermissionName::CompanyView->value,
            PermissionName::StocksView->value,
            PermissionName::StocksUpdate->value,
            PermissionName::CurrenciesView->value,
            PermissionName::CategoriesView->value,
            PermissionName::CategoriesCreate->value,
            PermissionName::CategoriesUpdate->value,
            PermissionName::ProductLabelsView->value,
            PermissionName::UnitsView->value,
            PermissionName::ProductsView->value,
            PermissionName::ProductsCreate->value,
            PermissionName::ProductsUpdate->value,
            PermissionName::StockItemsView->value,
            PermissionName::StockMovementsView->value,
            PermissionName::StockMovementsCreate->value,
            PermissionName::StockMovementsUpdate->value,
            PermissionName::StockMovementsValidate->value,
            PermissionName::StockMovementsCancel->value,
            PermissionName::SalesView->value,
            PermissionName::InvoicesView->value,
            PermissionName::ExpensesView->value,
        ]);
    }
}
