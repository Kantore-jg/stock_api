<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('amount', 19, 4);
            $table->decimal('exchange_rate', 19, 8)->default(1);
            $table->string('reference', 120)->nullable();
            $table->dateTime('payment_date');
            $table->string('status', 20)->default('confirmed');
            $table->unsignedBigInteger('cash_session_id')->nullable()->index();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
