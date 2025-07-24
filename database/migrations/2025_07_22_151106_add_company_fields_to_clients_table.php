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
        Schema::table('clients', function (Blueprint $table) {
            // Informations société
            $table->string('raison_sociale')->nullable();
            $table->string('nom_commercial')->nullable();
            $table->string('siret')->nullable();
            $table->string('adresse')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('ville')->nullable();

            // Responsable principal
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_prenom')->nullable();
            $table->string('responsable_email')->nullable();
            $table->string('responsable_telephone')->nullable();
            $table->string('responsable_fonction')->nullable();

            // Activité
            $table->text('activite_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'raison_sociale', 'nom_commercial', 'siret', 'adresse', 'code_postal', 'ville',
                'responsable_nom', 'responsable_prenom', 'responsable_email',
                'responsable_telephone', 'responsable_fonction', 'activite_description',
            ]);
        });
    }
};
