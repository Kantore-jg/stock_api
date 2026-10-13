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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('legal_name', 200)->nullable();
            $table->string('tax_number', 80)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->foreignId('default_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
