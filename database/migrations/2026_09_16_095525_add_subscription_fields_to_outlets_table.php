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
        Schema::table('outlets', function (Blueprint $table) {
              $table->enum('plan_type', ['trial', 'paid'])->default('trial')->after('is_active');
            $table->date('trial_ends_at')->nullable()->after('plan_type');
            $table->enum('subscription_status', ['active', 'expired', 'terminated'])
                  ->default('active')
                  ->after('trial_ends_at');
            $table->decimal('monthly_amount', 10, 2)->nullable()->after('subscription_status');
 
            // Staff limits (set by Super Admin). 0 = no salesman/booker allowed, null = unlimited.
            $table->unsignedInteger('max_salesmen')->nullable()->after('monthly_amount');
            $table->unsignedInteger('max_bookers')->nullable()->after('max_salesmen');
 
            // Manual billing trail
            $table->date('last_marked_paid_at')->nullable()->after('max_bookers');
            $table->foreignId('marked_paid_by')->nullable()->after('last_marked_paid_at')
                  ->constrained('users')->nullOnDelete();
 
            // Termination trail — access is blocked via subscription_status, data is never deleted
            $table->timestamp('terminated_at')->nullable()->after('marked_paid_by');
            $table->foreignId('terminated_by')->nullable()->after('terminated_at')
                  ->constrained('users')->nullOnDelete();
            $table->text('termination_note')->nullable()->after('terminated_by');
 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
             $table->dropConstrainedForeignId('marked_paid_by');
            $table->dropConstrainedForeignId('terminated_by');
 
            $table->dropColumn([
                'plan_type',
                'trial_ends_at',
                'subscription_status',
                'monthly_amount',
                'max_salesmen',
                'max_bookers',
                'last_marked_paid_at',
                'terminated_at',
                'termination_note',
                'deleted_at',
            ]);F
        });
    }
};
