<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->enum('airport', ['ORY', 'CDG', 'LBG'])->nullable()->after('coworker_id');
        });

        DB::statement('
            UPDATE badges b
            JOIN badge_requests br ON b.badge_request_id = br.id
            JOIN activity_requests ar ON br.activity_request_id = ar.id
            SET b.airport = ar.airport
            WHERE b.airport IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('airport');
        });
    }
};
