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
            $table->timestamps();

            // Créateur de la demande du laissez-passer
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Relation avec la table clients
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            // Informations sur le laissez-passer
            $table->enum('airport', ['ORY', 'CDG', 'LBG'])->nullable();
            $table->string('plate_number')->nullable();
            $table->string('car_brand')->nullable();

            // Gestion du statut du laissez-passer
            $table->enum('status', [
                'pending',
                'rejected',
                'approved',
            ])->default('pending');
            $table->timestamp('pending_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('previous_status')->nullable();
            $table->text('reject_reason')->nullable();

            // Chemin des documents
            $table->string('certificate_of_registration')->nullable(); // Carte grise
            $table->string('company_stamp')->nullable(); // Tampon de l'entreprise

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
