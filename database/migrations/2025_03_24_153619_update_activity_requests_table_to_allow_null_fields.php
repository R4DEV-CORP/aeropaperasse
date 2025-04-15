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
        Schema::table('allow_null_fields', function (Blueprint $table) {
            Schema::table('activity_requests', function (Blueprint $table) {
                $table->string('raison_sociale')->nullable()->change();
                $table->string('nom_commercial')->nullable()->change();
                $table->string('siret')->nullable()->change();
                $table->string('adresse')->nullable()->change();
                $table->string('responsable_nom')->nullable()->change();
                $table->string('responsable_prenom')->nullable()->change();
                $table->string('responsable_email')->nullable()->change();
                $table->string('responsable_telephone')->nullable()->change();
                $table->string('responsable_fonction')->nullable()->change();
                $table->text('activite_description')->nullable()->change();
                $table->integer('nombre_personnes')->nullable()->change();
                $table->integer('nombre_vehicules')->nullable()->change();
                $table->string('clients_denomination')->nullable()->change();
                $table->string('extrait_kbis_path')->nullable()->change();
                $table->string('attestations_clients_path')->nullable()->change();
                $table->string('formulaire_surete_path')->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allow_null_fields', function (Blueprint $table) {
            Schema::table('activity_requests', function (Blueprint $table) {
                $table->string('raison_sociale')->nullable(false)->change();
                $table->string('nom_commercial')->nullable(false)->change();
                $table->string('siret')->nullable(false)->change();
                $table->string('adresse')->nullable(false)->change();
                $table->string('responsable_nom')->nullable(false)->change();
                $table->string('responsable_prenom')->nullable(false)->change();
                $table->string('responsable_email')->nullable(false)->change();
                $table->string('responsable_telephone')->nullable(false)->change();
                $table->string('responsable_fonction')->nullable(false)->change();
                $table->text('activite_description')->nullable(false)->change();
                $table->integer('nombre_personnes')->nullable(false)->change();
                $table->integer('nombre_vehicules')->nullable(false)->change();
                $table->string('clients_denomination')->nullable(false)->change();
                $table->string('extrait_kbis_path')->nullable(false)->change();
                $table->string('attestations_clients_path')->nullable(false)->change();
                $table->string('formulaire_surete_path')->nullable(false)->change();
            });
        });
    }
};
