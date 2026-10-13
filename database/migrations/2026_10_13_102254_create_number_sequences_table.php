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
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->string('document_type', 40);
            $table->string('prefix', 20);
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedInteger('padding')->default(6);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['company_id', 'stock_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
