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
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('badge_number');

            $table->unsignedBigInteger('badge_request_id')->nullable()->change();
            $table->date('expiry_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('badge_number')->unique();

            $table->unsignedBigInteger('badge_request_id')->nullable(false)->change();
            $table->date('expiry_date')->nullable(false)->change();
        });
    }
};
