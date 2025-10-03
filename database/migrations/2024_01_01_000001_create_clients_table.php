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
            $table->string('name');
            $table->string('referent_name')->nullable();
            $table->string('referent_email')->nullable();
            $table->timestamps();
            $table->string('safety_referent_name_1')->nullable();
            $table->string('safety_referent_email_1')->nullable();
            $table->string('safety_referent_phone_1')->nullable();
            $table->string('safety_referent_name_2')->nullable();
            $table->string('safety_referent_email_2')->nullable();
            $table->string('safety_referent_phone_2')->nullable();
            $table->string('safety_referent_name_3')->nullable();
            $table->string('safety_referent_email_3')->nullable();
            $table->string('safety_referent_phone_3')->nullable();
            $table->string('security_correspondent_name')->nullable();
            $table->string('security_correspondent_email')->nullable();
            $table->string('security_correspondent_phone')->nullable();
            $table->string('kbis_document')->nullable();
            $table->string('hr_contact_name')->nullable();
            $table->string('hr_contact_email')->nullable();
            $table->string('hr_contact_phone')->nullable();
            $table->string('safety_document')->nullable();
            $table->string('security_document')->nullable();
            $table->integer('badge_limit')->nullable();
            $table->integer('vehicle_pass_limit')->nullable();
            $table->string('notification_email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('siret_number')->nullable();
            $table->string('company_website')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('sous_traitant_de')->nullable();
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
