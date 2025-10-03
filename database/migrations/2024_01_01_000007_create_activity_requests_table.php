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
        Schema::create('activity_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('renouvellement')->default(false);
            $table->string('autorisation_anterieur')->nullable();
            $table->string('raison_sociale')->nullable();
            $table->string('nom_commercial')->nullable();
            $table->string('siret')->nullable();
            $table->string('adresse')->nullable();
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_prenom')->nullable();
            $table->string('responsable_email')->nullable();
            $table->string('responsable_telephone')->nullable();
            $table->string('responsable_fonction')->nullable();
            $table->text('activite_description')->nullable();
            $table->integer('nombre_personnes')->nullable();
            $table->integer('nombre_vehicules')->nullable();
            $table->string('clients_denomination')->nullable();
            $table->string('extrait_kbis_path')->nullable();
            $table->string('attestations_clients_path')->nullable();
            $table->string('formulaire_surete_path')->nullable();
            $table->string('agrement_prefectoral_path')->nullable();
            $table->string('contrat_iata_path')->nullable();
            $table->string('cta_path')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->string('previous_status')->nullable();
            $table->timestamp('draft_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('pending_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
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
