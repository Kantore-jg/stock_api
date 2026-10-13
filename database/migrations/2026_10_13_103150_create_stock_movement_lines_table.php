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
        Schema::create('stock_movement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_cost', 19, 4)->nullable();
            $table->decimal('source_quantity_before', 19, 4)->nullable();
            $table->decimal('source_quantity_after', 19, 4)->nullable();
            $table->decimal('destination_quantity_before', 19, 4)->nullable();
            $table->decimal('destination_quantity_after', 19, 4)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_lines');
    }
};
