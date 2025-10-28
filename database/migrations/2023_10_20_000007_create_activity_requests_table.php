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
            $table->timestamps();

            // Relation avec la table clients
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');

            // Relation avec la table user : créateur de la demande
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            // Aéroport concerné
            $table->enum('airport', ['ORY', 'CDG', 'LBG'])->nullable();

            // Gestion du renouvellement
            $table->boolean('renewal')->default(false); // renouvellement
            $table->unsignedBigInteger('last_activity_request_id')->nullable();
            $table->foreign('last_activity_request_id')->references('id')->on('activity_requests');

            // Responsable de l'activité
            $table->string('manager_firstname')->nullable();
            $table->string('manager_lastname')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_role')->nullable();

            // Informations sur l'activité
            $table->text('description')->nullable();
            $table->integer('person_count')->default(1);
            $table->integer('vehicule_count')->default(0);
            $table->string('customer_names')->nullable(); // dénomination des clients

            // Chemin des documents
            $table->string('customer_certificate_document')->nullable(); // attestation client
            $table->string('prefectural_agreement_document')->nullable(); // agrément préfectoral
            $table->string('iata_contract_document')->nullable(); // contrat IATA
            $table->string('cta_document')->nullable(); // CTA

            // Statut de la demande
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->string('previous_status')->default('draft');
            $table->timestamp('draft_at')->nullable();
            $table->timestamp('pending_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Raison du refus
            $table->text('reject_reason')->nullable();
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
