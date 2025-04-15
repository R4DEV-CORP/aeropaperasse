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
            $table->boolean('est_habilitation')->default(false);
            $table->string('document_for')->nullable();
            $table->string('facture')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->dropColumn('est_habilitation');
            $table->dropColumn('document_for');
            $table->dropColumn('facture');
        });
    }
};
