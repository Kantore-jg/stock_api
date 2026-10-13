<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name_snapshot', 180);
            $table->string('product_sku_snapshot', 100)->nullable();
            $table->string('label_name_snapshot', 180);
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_price', 19, 4);
            $table->decimal('discount_amount', 19, 4)->default(0);
            $table->decimal('tax_rate_snapshot', 7, 4)->default(0);
            $table->decimal('tax_amount', 19, 4)->default(0);
            $table->decimal('line_subtotal', 19, 4);
            $table->decimal('line_total', 19, 4);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
