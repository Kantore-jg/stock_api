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
        Schema::create('stock_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('can_view')->default(true);
            $table->boolean('can_manage_stock')->default(false);
            $table->boolean('can_sell')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamp('assigned_at')->nullable();

            $table->unique(['stock_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_user');
    }
};
