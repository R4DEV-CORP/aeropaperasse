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
        Schema::table('vehicle_passes', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_request_id')->nullable()->after('client_id');
            $table->foreign('activity_request_id')->references('id')->on('activity_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_passes', function (Blueprint $table) {
            $table->dropForeign(['activity_request_id']);
            $table->dropColumn('activity_request_id');
        });
    }
};
