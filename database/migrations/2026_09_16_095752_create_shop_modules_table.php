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
        Schema::create('shop_modules', function (Blueprint $table) {
             $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
 
            // One of: customers, suppliers, purchase_orders, invoices,
            // bookings, products, inventory, profit_loss, pos
            $table->string('module_key', 50);
 
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('disabled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disabled_note', 300)->nullable();
 
            $table->timestamps();
 
            $table->unique(['outlet_id', 'module_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_modules');
    }
};
