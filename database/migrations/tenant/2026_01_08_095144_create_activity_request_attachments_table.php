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
        Schema::create('activity_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('activity_request_id')->constrained('activity_requests')->onDelete('cascade');
            $table->string('path');
            $table->string('type'); // aao_request, kbis, principals, safety_referent, security_referent, cta
            $table->string('name');

            // Index pour améliorer les performances des requêtes par type
            $table->index('type');
            $table->index(['activity_request_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_request_attachments');
    }
};
