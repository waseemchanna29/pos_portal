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
        DB::statement(
            "ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'salesman', 'booker') NOT NULL DEFAULT 'salesman'"
        );

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'booker')->update(['role' => 'salesman']);

        DB::statement(
            "ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'salesman') NOT NULL DEFAULT 'salesman'"
        );

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
