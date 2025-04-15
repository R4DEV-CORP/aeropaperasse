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
                'rejected_rem',
                'pending_adp',
                'approved_adp',
                'rejected_adp',
                'pending_fabrication',
                'ready_for_delivery'
            ])->default('pending_rem')->after('telephone');

            $table->dropColumn('approved_rem_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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

            $table->timestamp('approved_rem_at')->nullable();
        });
    }
};
