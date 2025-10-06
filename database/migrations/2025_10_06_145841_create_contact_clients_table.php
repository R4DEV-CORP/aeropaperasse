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
        Schema::create('contact_clients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            //Relation avec la table clients
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');

            // Informations du contact
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');
            $table->string('function')->enum(['hr', 'safety', 'security', 'manager']); // manager = responsable, hr = ressources humaines, safety = sureté, security = sécurité
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_clients');
    }
};
