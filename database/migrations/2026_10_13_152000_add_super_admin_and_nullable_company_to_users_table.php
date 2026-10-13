<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('is_active');
            }
        });

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
            });
        } catch (\Throwable) {
            // ignore
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['company_id', 'email']);
            });
        } catch (\Throwable) {
            // ignore
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        } catch (\Throwable) {
            // ignore
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            // SQLite rebuilds the table for nullable changes via doctrine-less approach:
            // recreate foreign as nullable using temporary column swap is heavy;
            // for tests, drop and re-add column is not ideal either.
            // Laravel's change() works on sqlite in recent versions without doctrine.
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE users MODIFY company_id BIGINT UNSIGNED NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->unique(['company_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'email']);
        });

        DB::table('users')->whereNull('company_id')->delete();

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable(false)->change();
            });
        } else {
            DB::statement('ALTER TABLE users MODIFY company_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'email']);
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }
};
