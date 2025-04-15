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
            $table->string('nom')->nullable()->change();
            $table->string('prenom')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('telephone')->nullable()->change();
            $table->string('photoIdentite')->nullable()->change();
            $table->string('autorisationActivite')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->string('nom')->nullable(false)->change();
            $table->string('prenom')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('telephone')->nullable(false)->change();
            $table->string('photoIdentite')->nullable(false)->change();
            $table->string('autorisationActivite')->nullable(false)->change();
        });
    }
};
