<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('product_label_id')->constrained('product_labels')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 19, 4)->default(0);
            $table->decimal('selling_price', 19, 4)->default(0);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('min_stock_level', 19, 4)->default(0);
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'barcode']);
            $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
