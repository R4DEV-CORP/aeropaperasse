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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Informations entreprise
            $table->string('company_name'); // raison sociale
            $table->string('trade_name'); // nom commercial
            $table->string('siret_number');
            $table->string('address');
            $table->string('zip_code');
            $table->string('city');
            $table->text('subcontractor_of')->nullable(); // Sous traitant de quelles entreprises

            // Chemins documents obligatoires
            $table->string('kbis_document');
            $table->string('safety_document');
            $table->string('security_document');

            // Limites de badges et de véhicules
            $table->integer('badge_limit')->default(1);
            $table->integer('vehicle_pass_limit')->default(1);

            // Autres informations
            $table->string('notification_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
