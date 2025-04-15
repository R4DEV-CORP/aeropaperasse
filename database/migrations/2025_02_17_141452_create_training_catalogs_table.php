<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingCatalogsTable extends Migration
{
    public function up()
    {
        Schema::create('training_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('dendreo_id')->unique();
            $table->string('title');
            $table->string('short_title')->nullable();
            $table->integer('validity_duration')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('category')->nullable();
            $table->string('parent_category')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('training_catalogs');
    }
}