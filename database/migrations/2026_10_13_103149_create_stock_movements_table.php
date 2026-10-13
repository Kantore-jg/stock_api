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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('reference', 60);
            $table->string('movement_type', 30);
            $table->string('status', 20)->default('draft');
            $table->foreignId('source_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('destination_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->dateTime('document_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference']);
            $table->index(['company_id', 'document_date']);
            $table->index(['company_id', 'status', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
