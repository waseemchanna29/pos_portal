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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
             $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'cheque',
                'easypaisa',
                'jazzcash',
            ])->default('cash');

            $table->string('reference_number')->nullable(); // cheque no / TXN id
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
