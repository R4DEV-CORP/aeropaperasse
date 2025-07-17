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
        Schema::create('vehicle_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('nom_entreprise');
            $table->string('siret', 14);
            $table->string('adresse');
            $table->string('code_postal', 10);
            $table->string('ville');
            $table->string('tampon_entreprise');
            $table->enum('aeroport', ['CDG', 'ORLY', 'BOURGET']);
            $table->string('immatriculation')->unique();
            $table->string('marque_vehicule');
            $table->string('carte_grise_path');
            $table->enum('status', ['pending', 'approved', 'rejected', 'draft'])->default('pending');
            $table->enum('previous_status', ['pending', 'approved', 'rejected', 'draft'])->nullable();
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_passes');
    }
};
