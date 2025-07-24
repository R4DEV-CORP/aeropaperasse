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
            $table->string('numero_identification')->nullable()->after('siret');

            // Contacts - Ajouter prénoms manquants
            $table->string('safety_referent_prenom_1')->nullable()->after('safety_referent_name_1');
            $table->string('safety_referent_prenom_2')->nullable()->after('safety_referent_name_2');
            $table->string('safety_referent_prenom_3')->nullable()->after('safety_referent_name_3');

            $table->string('security_correspondent_prenom')->nullable()->after('security_correspondent_name');

            $table->string('hr_contact_prenom')->nullable()->after('hr_contact_name');

            // Activité - Arrays JSON
            $table->json('aeroports_concernes')->nullable()->after('activite_description');
            $table->json('zones_concernees')->nullable()->after('aeroports_concernes');

            // Activité - Champs manuels
            $table->integer('nombre_demandes_activite')->nullable()->default(0)->after('zones_concernees');
            $table->text('numeros_demandes_activite')->nullable()->after('nombre_demandes_activite'); // Ex: "DA-2024-001, DA-2024-015"
            $table->date('date_debut_validite')->nullable()->after('numeros_demandes_activite');
            $table->date('date_fin_validite')->nullable()->after('date_debut_validite');
            $table->integer('nombre_badges_actifs')->nullable()->default(0)->after('date_fin_validite');
            $table->integer('nombre_vehicules_actifs')->nullable()->default(0)->after('nombre_badges_actifs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'numero_identification',
                'safety_referent_prenom_1',
                'safety_referent_prenom_2',
                'safety_referent_prenom_3',
                'security_correspondent_prenom',
                'hr_contact_prenom',
                'aeroports_concernes',
                'zones_concernees',
                'nombre_demandes_activite',
                'numeros_demandes_activite',
                'date_debut_validite',
                'date_fin_validite',
                'nombre_badges_actifs',
                'nombre_vehicules_actifs'
            ]);
        });
    }
};
