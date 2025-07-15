<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Désactiver temporairement les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider les tables qui référencent trainings
        DB::table('client_training_access')->truncate();
        DB::table('user_trainings')->truncate();
        DB::table('trainings')->truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Modifier la table trainings - supprimer les colonnes Dendreo
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn([
                'dendreo_id',
                'short_title',
                'duration_hours',
                'duration_days',
                'validity_duration',
                'category',
                'parent_category'
            ]);
        });

        // Ajouter validity_years à user_trainings
        Schema::table('user_trainings', function (Blueprint $table) {
            $table->integer('validity_years')->nullable()->after('training_id');
        });

        // Insérer les nouvelles formations
        $formations = [
            '11.2.6.2 (dit TCA)',
            'Sécurité piétons',
            'Permis T',
            '11.2.3.9',
            '11.2.3.10',
            'Pratique permis T',
            '11.2.3.9 plus TCA',
            '11.2.3.10 plus TCA',
            'Facteur humain',
            'Co activité'
        ];

        foreach ($formations as $formation) {
            DB::table('trainings')->insert([
                'title' => $formation,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        // Désactiver temporairement les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider les nouvelles formations
        DB::table('trainings')->truncate();
        DB::table('client_training_access')->truncate();
        DB::table('user_trainings')->truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Restaurer les colonnes trainings
        Schema::table('trainings', function (Blueprint $table) {
            $table->string('dendreo_id')->nullable();
            $table->string('short_title')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('validity_duration')->nullable();
            $table->string('category')->nullable();
            $table->string('parent_category')->nullable();
        });

        // Supprimer validity_years
        Schema::table('user_trainings', function (Blueprint $table) {
            $table->dropColumn('validity_years');
        });
    }
};
