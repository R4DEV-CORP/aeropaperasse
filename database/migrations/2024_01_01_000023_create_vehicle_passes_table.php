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
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('client_id');

            $table->enum('airport', ['ORY', 'CDG', 'LBG']);
            $table->string('plate_number');
            $table->string('car_brand');

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

            $table->string('certificate_of_registration'); // Carte grise
            $table->string('company_stamp'); // Tampon de l'entreprise

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
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
