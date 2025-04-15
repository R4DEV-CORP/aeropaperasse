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
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->string('telephone');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('photoIdentite'); // Chemin du fichier
            $table->string('autorisationActivite'); // Chemin du fichier
            $table->string('certificatFormation'); // Chemin du fichier
            $table->timestamps();
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
