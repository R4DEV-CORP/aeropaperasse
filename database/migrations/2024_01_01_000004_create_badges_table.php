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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('badge_request_id');
            $table->string('badge_number')->unique();
            $table->enum('status', ['active', 'expired', 'returned', 'not_returned'])->default('active');
            $table->string('previous_status')->nullable();
            $table->date('expiry_date');
            $table->timestamp('returned_at')->nullable();
            $table->string('return_document')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('badge_request_id')->references('id')->on('badge_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
