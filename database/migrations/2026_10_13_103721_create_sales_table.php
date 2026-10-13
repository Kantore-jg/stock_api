<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained('stocks')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('invoice_number', 60)->nullable();
            $table->string('status', 25)->default('draft');
            $table->dateTime('sale_date');
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 19, 8)->default(1);
            $table->decimal('subtotal', 19, 4)->default(0);
            $table->decimal('discount_amount', 19, 4)->default(0);
            $table->decimal('tax_amount', 19, 4)->default(0);
            $table->decimal('total_amount', 19, 4)->default(0);
            $table->decimal('amount_paid', 19, 4)->default(0);
            $table->decimal('balance_due', 19, 4)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'sale_date']);
            $table->index(['stock_id', 'status', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
