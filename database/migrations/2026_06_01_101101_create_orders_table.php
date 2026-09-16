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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relationships
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salesman_id')->nullable()->constrained('users')->nullOnDelete();

            // Order identity
            $table->string('order_number')->unique();
            $table->enum('order_type', ['pos', 'booking'])->default('pos');

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'dispatched',
                'delivered',
                'completed',
                'cancelled',
            ])->default('pending');

            // Payment status
            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
            ])->default('unpaid');

            // Customer info
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_city', 100)->nullable();

            // Financials
            $table->decimal('subtotal',        12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount',      12, 2)->default(0);
            $table->decimal('net_amount',      12, 2)->default(0);
            $table->decimal('paid_amount',     12, 2)->default(0);
            $table->decimal('balance_amount',  12, 2)->default(0);

            // Dates
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivered_date')->nullable();

            // Extra
            $table->text('notes')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
