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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('dendreo_id');
            $table->string('title');
            $table->string('short_title')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('validity_duration')->nullable();
            $table->string('category')->nullable();
            $table->string('parent_category')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('private');
            $table->integer('duration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
