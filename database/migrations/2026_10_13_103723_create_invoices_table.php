<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->string('invoice_number', 60);
            $table->dateTime('invoice_date');
            $table->string('status', 20)->default('issued');
            $table->string('customer_name_snapshot', 180)->nullable();
            $table->text('customer_address_snapshot')->nullable();
            $table->string('customer_tax_number_snapshot', 80)->nullable();
            $table->decimal('subtotal', 19, 4);
            $table->decimal('discount_amount', 19, 4);
            $table->decimal('tax_amount', 19, 4);
            $table->decimal('total_amount', 19, 4);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->unique('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
