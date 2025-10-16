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
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Informations sur le collaborateur
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');

            // Informations état collaborateur
            $table->boolean('has_leave')->default(false);
            $table->date('departure_date')->nullable();
            $table->boolean('can_access_formation')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('coworker_id')->nullable();
            $table->foreign('coworker_id')->references('id')->on('coworkers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coworkers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['coworker_id']);
            $table->dropColumn('coworker_id');
        });
    }
};
