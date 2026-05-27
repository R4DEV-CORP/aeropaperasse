<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('coworker_trainings', 'airport')) {
            Schema::table('coworker_trainings', function (Blueprint $table) {
                $table->enum('airport', ['ORY', 'CDG', 'LBG'])->nullable()->after('training_id');
            });
        }

        Schema::table('coworker_trainings', function (Blueprint $table) {
            $table->unique(['coworker_id', 'training_id', 'airport']);
        });

        Schema::table('coworker_trainings', function (Blueprint $table) {
            $table->dropUnique(['coworker_id', 'training_id']);
        });
    }

    public function down(): void
    {
        Schema::table('coworker_trainings', function (Blueprint $table) {
            $table->dropUnique(['coworker_id', 'training_id', 'airport']);
            $table->unique(['coworker_id', 'training_id']);
            $table->dropColumn('airport');
        });
    }
};
