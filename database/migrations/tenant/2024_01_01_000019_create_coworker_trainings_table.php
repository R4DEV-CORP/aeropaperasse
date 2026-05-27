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
        Schema::create('coworker_trainings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coworker_id');
            $table->unsignedBigInteger('training_id');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->string('certificate_path')->nullable();
            $table->timestamps();

            $table->unique(['coworker_id', 'training_id']);
            $table->foreign('coworker_id')->references('id')->on('coworkers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coworker_trainings');
    }
};
