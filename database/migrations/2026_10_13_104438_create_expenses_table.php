<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
            $table->string('reference', 60);
            $table->text('description');
            $table->decimal('amount', 19, 4);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->restrictOnDelete();
            $table->dateTime('expense_date');
            $table->string('status', 20)->default('validated');
            $table->string('receipt_path', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'reference']);
            $table->index(['company_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
