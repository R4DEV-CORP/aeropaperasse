<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('activity_requests', function (Blueprint $table) {
            $table->id();
            $table->boolean('renouvellement')->default(false);
            $table->string('autorisation_anterieur')->nullable();
            $table->string('raison_sociale');
            $table->string('nom_commercial');
            $table->string('siret');
            $table->string('adresse');
            $table->string('responsable_nom');
            $table->string('responsable_prenom');
            $table->string('responsable_email');
            $table->string('responsable_telephone');
            $table->string('responsable_fonction');
            $table->text('activite_description');
            $table->integer('nombre_personnes');
            $table->integer('nombre_vehicules');
            $table->string('clients_denomination');
            $table->string('extrait_kbis_path');
            $table->string('attestations_clients_path');
            $table->string('formulaire_surete_path');
            $table->string('agrement_prefectoral_path')->nullable();
            $table->string('contrat_iata_path')->nullable();
            $table->string('cta_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_requests');
    }
};
