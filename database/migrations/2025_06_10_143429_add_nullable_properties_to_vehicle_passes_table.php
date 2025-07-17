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
            $table->string('adresse', 500)->nullable()->change();
            $table->string('code_postal', 10)->nullable()->change();
            $table->string('tampon_entreprise')->nullable()->change();
            $table->string('aeroport')->nullable()->change();
            $table->string('immatriculation', 20)->nullable()->change();
            $table->string('marque_vehicule', 100)->nullable()->change();
            $table->string('carte_grise_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_passes', function (Blueprint $table) {
            $table->string('adresse', 500)->nullable(false)->change();
            $table->string('code_postal', 10)->nullable(false)->change();
            $table->string('tampon_entreprise')->nullable(false)->change();
            $table->string('aeroport')->nullable(false)->change();
            $table->string('immatriculation', 20)->nullable(false)->change();
            $table->string('marque_vehicule', 100)->nullable(false)->change();
            $table->string('carte_grise_path')->nullable(false)->change();
        });
    }
};
