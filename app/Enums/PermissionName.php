<?php

namespace App\Enums;

enum PermissionName: string
{
    case CompanyView = 'company.view';
    case CompanyUpdate = 'company.update';

    case StocksView = 'stocks.view';
    case StocksCreate = 'stocks.create';
    case StocksUpdate = 'stocks.update';
    case StocksDelete = 'stocks.delete';
    case StocksAssignUsers = 'stocks.assign_users';

    case CurrenciesView = 'currencies.view';

    case SettingsView = 'settings.view';
    case SettingsUpdate = 'settings.update';

    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';

    case ProductLabelsView = 'product_labels.view';
    case ProductLabelsCreate = 'product_labels.create';
    case ProductLabelsUpdate = 'product_labels.update';
    case ProductLabelsDelete = 'product_labels.delete';

    case UnitsView = 'units.view';
    case UnitsCreate = 'units.create';
    case UnitsUpdate = 'units.update';
    case UnitsDelete = 'units.delete';

    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsUpdate = 'products.update';
    case ProductsDelete = 'products.delete';

    case StockItemsView = 'stock_items.view';

    case StockMovementsView = 'stock_movements.view';
    case StockMovementsCreate = 'stock_movements.create';
    case StockMovementsUpdate = 'stock_movements.update';
    case StockMovementsValidate = 'stock_movements.validate';
    case StockMovementsCancel = 'stock_movements.cancel';

    case CustomersView = 'customers.view';
    case CustomersCreate = 'customers.create';
    case CustomersUpdate = 'customers.update';
    case CustomersDelete = 'customers.delete';

    case SalesView = 'sales.view';
    case SalesCreate = 'sales.create';
    case SalesUpdate = 'sales.update';
    case SalesValidate = 'sales.validate';
    case SalesCancel = 'sales.cancel';
    case SalesPay = 'sales.pay';

    case InvoicesView = 'invoices.view';

    case PaymentsView = 'payments.view';
    case PaymentMethodsView = 'payment_methods.view';

    case CashRegistersView = 'cash_registers.view';
    case CashRegistersCreate = 'cash_registers.create';
    case CashRegistersUpdate = 'cash_registers.update';
    case CashSessionsOpen = 'cash_sessions.open';
    case CashSessionsClose = 'cash_sessions.close';
    case CashSessionsView = 'cash_sessions.view';
    case CashMovementsCreate = 'cash_movements.create';

    case ExpenseCategoriesView = 'expense_categories.view';
    case ExpenseCategoriesManage = 'expense_categories.manage';
    case ExpensesView = 'expenses.view';
    case ExpensesCreate = 'expenses.create';
    case ExpensesValidate = 'expenses.validate';
    case ExpensesCancel = 'expenses.cancel';

    case AuditLogsView = 'audit_logs.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
