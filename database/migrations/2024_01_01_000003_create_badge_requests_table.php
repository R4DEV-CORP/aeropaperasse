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
            $table->unsignedBigInteger('user_id');
            $table->string('airport')->nullable();
            $table->timestamp('draft_at')->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->enum('status', [
                'draft',
                'pending_rem',
                'rejected_rem',
                'pending_adp',
                'approved_adp',
                'rejected_adp',
                'pending_fabrication',
                'ready_for_delivery'
            ])->default('pending_rem');
            $table->string('previous_status')->nullable();
            $table->text('reject_reason')->nullable();
            $table->string('photoIdentite')->nullable();
            $table->string('pieceIdentite')->nullable();
            $table->string('autorisationActivite')->nullable();
            $table->string('certificatFormation')->nullable();
            $table->timestamps();
            $table->timestamp('pending_rem_at')->nullable();
            $table->timestamp('rejected_rem_at')->nullable();
            $table->timestamp('pending_adp_at')->nullable();
            $table->timestamp('approved_adp_at')->nullable();
            $table->timestamp('rejected_adp_at')->nullable();
            $table->timestamp('pending_fabrication_at')->nullable();
            $table->timestamp('ready_for_delivery_at')->nullable();
            $table->boolean('est_habilitation')->default(false);
            $table->string('documentFor')->nullable();
            $table->string('facture')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
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
