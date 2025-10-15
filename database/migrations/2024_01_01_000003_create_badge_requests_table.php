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
        Schema::create('badge_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Créateur de la demande de badge
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            // Relation avec la demande d'activité
            $table->unsignedBigInteger('activity_request_id');
            $table->foreign('activity_request_id')->references('id')->on('activity_requests');

            // Informations / Relation sur le bénéficiaire du badge
            $table->unsignedBigInteger('coworker_id');
            $table->foreign('coworker_id')->references('id')->on('coworkers');

            // Gestion du statut de la demande de badge
            $table->enum('status', [
                'draft',
                'pending_rem',
                'rejected_rem',
                'pending_adp',
                'approved_adp',
                'rejected_adp',
                'pending_fabrication',
                'ready_for_delivery',
                'terminated',
            ])->default('pending_rem');
            $table->string('previous_status')->nullable();
            $table->timestamp('draft_at')->nullable();
            $table->timestamp('pending_rem_at')->nullable();
            $table->timestamp('rejected_rem_at')->nullable();
            $table->timestamp('pending_adp_at')->nullable();
            $table->timestamp('approved_adp_at')->nullable();
            $table->timestamp('rejected_adp_at')->nullable();
            $table->timestamp('pending_fabrication_at')->nullable();
            $table->timestamp('ready_for_delivery_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->text('reject_reason')->nullable();

            // Chemin des documents
            $table->string('selfie_photo')->nullable(); // Photo d'identité
            $table->string('identification_card')->nullable(); // Carte d'identité
            $table->string('activity_authorization')->nullable(); // Autorisation d'activité
            $table->string('for_document')->nullable(); // Document FOR
            $table->string('formation_certificate_document')->nullable(); // Certificat de formation
            $table->string('invoice_document')->nullable(); // Facture

            // Informations sur la demande de badge
            $table->boolean('application_authorization')->default(false); // Demande d'habillitation
            $table->boolean('validate_training')->default(false); // Validation de la formation par la société
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_requests');
    }
};
