<?php

use App\Http\Controllers\Api\Admin\TenantCompanyController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\CashSessionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanySettingController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NumberSequenceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductLabelController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\StockItemController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\StockUserController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SetPermissionsTeamId;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me'])->middleware(SetPermissionsTeamId::class);
    });
});

Route::middleware(['auth:sanctum', EnsureSuperAdmin::class])->prefix('admin')->group(function () {
    Route::get('companies', [TenantCompanyController::class, 'index']);
    Route::post('companies', [TenantCompanyController::class, 'store']);
    Route::get('companies/{company}', [TenantCompanyController::class, 'show']);
    Route::put('companies/{company}', [TenantCompanyController::class, 'update']);
});

Route::middleware(['auth:sanctum', SetPermissionsTeamId::class])->group(function () {
    Route::get('company', [CompanyController::class, 'show']);
    Route::put('company', [CompanyController::class, 'update']);

    Route::get('currencies', [CurrencyController::class, 'index']);
    Route::get('currencies/{currency}', [CurrencyController::class, 'show']);

    Route::apiResource('stocks', StockController::class);
    Route::get('stocks/{stock}/users', [StockUserController::class, 'index']);
    Route::post('stocks/{stock}/users', [StockUserController::class, 'store']);
    Route::put('stocks/{stock}/users/{user}', [StockUserController::class, 'update']);
    Route::delete('stocks/{stock}/users/{user}', [StockUserController::class, 'destroy']);
    Route::get('stocks/{stock}/items', [StockItemController::class, 'byStock']);

    Route::get('stock-items', [StockItemController::class, 'index']);

    Route::post('stock-movements/{stock_movement}/validate', [StockMovementController::class, 'validateMovement']);
    Route::post('stock-movements/{stock_movement}/cancel', [StockMovementController::class, 'cancel']);
    Route::apiResource('stock-movements', StockMovementController::class);

    Route::get('company-settings', [CompanySettingController::class, 'index']);
    Route::put('company-settings', [CompanySettingController::class, 'upsert']);

    Route::get('number-sequences', [NumberSequenceController::class, 'index']);
    Route::post('number-sequences', [NumberSequenceController::class, 'store']);
    Route::put('number-sequences/{number_sequence}', [NumberSequenceController::class, 'update']);
    Route::delete('number-sequences/{number_sequence}', [NumberSequenceController::class, 'destroy']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('product-labels', ProductLabelController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('products', ProductController::class);

    Route::apiResource('customers', CustomerController::class);

    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('sales/{sale}/payments', [PaymentController::class, 'store']);

    Route::post('sales/{sale}/validate', [SaleController::class, 'validateSale']);
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel']);
    Route::apiResource('sales', SaleController::class);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);

    Route::apiResource('cash-registers', CashRegisterController::class);
    Route::get('cash-sessions', [CashSessionController::class, 'index']);
    Route::post('cash-registers/{cash_register}/sessions', [CashSessionController::class, 'open']);
    Route::get('cash-sessions/{cash_session}', [CashSessionController::class, 'show']);
    Route::post('cash-sessions/{cash_session}/close', [CashSessionController::class, 'close']);
    Route::post('cash-sessions/{cash_session}/movements', [CashSessionController::class, 'storeMovement']);

    Route::get('expense-categories', [ExpenseCategoryController::class, 'index']);
    Route::post('expense-categories', [ExpenseCategoryController::class, 'store']);
    Route::put('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update']);
    Route::delete('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'destroy']);

    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);
    Route::get('expenses/{expense}', [ExpenseController::class, 'show']);
    Route::post('expenses/{expense}/validate', [ExpenseController::class, 'validateExpense']);
    Route::post('expenses/{expense}/cancel', [ExpenseController::class, 'cancel']);

    Route::get('audit-logs', [AuditLogController::class, 'index']);
});
