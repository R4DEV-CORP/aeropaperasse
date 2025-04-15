<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Modifier la table trainings
        Schema::table('trainings', function (Blueprint $table) {
            // Supprimer les colonnes existantes

            
            // Ajouter les nouvelles colonnes
            $table->string('dendreo_id')->after('id');
            $table->integer('validity_duration')->nullable()->after('title');
        });

        // Modifier la table user_trainings
        Schema::table('user_trainings', function (Blueprint $table) {
            // Supprimer les colonnes non nécessaires
            $table->dropColumn([
                'status',
                'completed_at'
            ]);
            
            // Ajouter la nouvelle colonne
            $table->string('certificate_path')->nullable()->after('expires_at');
        });
    }

    public function down()
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Restaurer les colonnes supprimées
            $table->dropColumn(['dendreo_id', 'validity_duration']);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
        });

        Schema::table('user_trainings', function (Blueprint $table) {
            // Restaurer les colonnes supprimées
            $table->dropColumn('certificate_path');
            $table->integer('progress')->default(0);
            $table->string('status')->default('not_started');
            $table->timestamp('completed_at')->nullable();
        });
    }
};