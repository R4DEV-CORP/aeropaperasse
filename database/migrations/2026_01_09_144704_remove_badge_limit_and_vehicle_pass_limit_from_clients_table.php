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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['badge_limit', 'vehicle_pass_limit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('badge_limit')->default(0)->after('is_airline_company');
            $table->unsignedInteger('vehicle_pass_limit')->default(0)->after('badge_limit');
        });
    }
};
