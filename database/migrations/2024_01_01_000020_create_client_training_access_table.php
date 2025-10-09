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
        Schema::create('client_training_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('training_id');
            $table->timestamp('access_starts_at')->nullable();
            $table->timestamp('access_expires_at')->nullable();
            $table->integer('max_users')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['client_id', 'training_id']);
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->index('status');
            $table->index('access_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_training_access');
    }
};
