<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('opening_amount', 19, 4)->default(0);
            $table->decimal('expected_amount', 19, 4)->nullable();
            $table->decimal('counted_amount', 19, 4)->nullable();
            $table->decimal('difference_amount', 19, 4)->nullable();
            $table->string('status', 20)->default('open');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('closing_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
