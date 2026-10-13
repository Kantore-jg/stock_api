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
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);

            $table->foreignId('company_id')->after('id')->constrained('companies')->cascadeOnDelete();
            $table->string('phone', 40)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->softDeletes();

            $table->unique(['company_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'email']);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['phone', 'is_active']);
            $table->unique('email');
        });
    }
};
