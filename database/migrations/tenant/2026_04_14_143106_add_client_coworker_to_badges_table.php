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
        Schema::table('badges', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->after('id');
            $table->unsignedBigInteger('coworker_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('badge_request_id')->nullable()->change();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('coworker_id')->references('id')->on('coworkers')->onDelete('cascade');
        });

        // Backfill client_id et coworker_id pour les badges existants
        DB::statement('
            UPDATE badges b
            JOIN badge_requests br ON b.badge_request_id = br.id
            JOIN activity_requests ar ON br.activity_request_id = ar.id
            SET b.client_id = ar.client_id, b.coworker_id = br.coworker_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['coworker_id']);
            $table->dropColumn(['client_id', 'coworker_id']);
            $table->unsignedBigInteger('badge_request_id')->nullable(false)->change();
        });
    }
};
