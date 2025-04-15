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
            $table->foreignId('badge_request_id')->constrained('badge_requests')->onDelete('cascade');
            $table->string('badge_number')->unique();
            $table->enum('status', ['active', 'expired', 'returned', 'not_returned'])->default('active');
            $table->date('expiry_date');
            $table->timestamp('returned_at')->nullable();
            $table->string('return_document')->nullable(); // Chemin vers le document de restitution signé
            $table->timestamps();
            $table->softDeletes(); // Pour garder l'historique
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
