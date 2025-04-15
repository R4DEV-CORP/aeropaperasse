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
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('badge_requests', function (Blueprint $table) {
            $table->enum('status', [
                'pending_rem',
                'approved_rem',
                'rejected_rem',
                'pending_adp',
                'approved_adp',
                'rejected_adp',
                'pending_fabrication',
                'ready_for_delivery'
            ])->default('pending_rem')->after('telephone');

            $table->timestamp('pending_rem_at')->nullable();
            $table->timestamp('approved_rem_at')->nullable();
            $table->timestamp('rejected_rem_at')->nullable();
            $table->timestamp('pending_adp_at')->nullable();
            $table->timestamp('approved_adp_at')->nullable();
            $table->timestamp('rejected_adp_at')->nullable();
            $table->timestamp('pending_fabrication_at')->nullable();
            $table->timestamp('ready_for_delivery_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'pending_rem_at',
                'approved_rem_at',
                'rejected_rem_at',
                'pending_adp_at',
                'approved_adp_at',
                'rejected_adp_at',
                'pending_fabrication_at',
                'ready_for_delivery_at'
            ]);
        });

        Schema::table('badge_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
    }
};
