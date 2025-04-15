<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ajouter les nouveaux champs à trainings
        Schema::table('trainings', function (Blueprint $table) {
            $table->string('short_title')->nullable()->after('title');
            $table->string('category')->nullable()->after('validity_duration');
            $table->string('parent_category')->nullable()->after('category');
        });

        // 2. Supprimer la table training_catalog
        Schema::dropIfExists('training_catalog');
    }

    public function down()
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['short_title', 'category', 'parent_category']);
        });
    }
};