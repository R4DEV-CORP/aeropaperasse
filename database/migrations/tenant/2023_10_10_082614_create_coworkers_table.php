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
        Schema::create('coworkers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Relation avec la table clients
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');

            // Relation avec la table users si le collaborateur a un compte
            $table->unsignedBigInteger('user_id')->nullable();

            // Informations sur le collaborateur
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');

            // Informations état collaborateur
            $table->boolean('has_leave')->default(false);
            $table->date('departure_date')->nullable();
        });

        // Note: the user ↔ coworker link is carried by `coworkers.user_id` (above).
        // The legacy reverse pointer `users.coworker_id` is a central column (added in
        // the central create_users migration, no cross-DB FK). See docs/multi-tenant-migration.md (Q-CLIENT).
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coworkers');
    }
};
