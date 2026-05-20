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
            $table->enum('role', ['superadmin', 'admin', 'salesman'])->default('salesman')->after('email');
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete()->after('role');
            $table->string('phone', 20)->nullable()->after('outlet_id');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropColumn(['role', 'outlet_id', 'phone', 'is_active']);
        });
    }
};
