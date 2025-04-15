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
        Schema::table('clients', function (Blueprint $table) {
            for ($i = 1; $i <= 3; $i++) {
                $table->string('safety_referent_name_'.$i)->nullable();
                $table->string('safety_referent_email_'.$i)->nullable();
                $table->string('safety_referent_phone_'.$i)->nullable();
                $table->string('safety_referent_document_'.$i)->nullable();
            }
            
            for ($i = 1; $i <= 3; $i++) {
                $table->string('security_correspondent_name_'.$i)->nullable();
                $table->string('security_correspondent_email_'.$i)->nullable();
                $table->string('security_correspondent_phone_'.$i)->nullable();
                $table->string('security_correspondent_document_'.$i)->nullable();
            }
            
            $table->string('kbis_document')->nullable();
            
            $table->string('hr_contact_name')->nullable();
            $table->string('hr_contact_email')->nullable();
            $table->string('hr_contact_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            for ($i = 1; $i <= 3; $i++) {
                $table->dropColumn('safety_referent_name_'.$i);
                $table->dropColumn('safety_referent_email_'.$i);
                $table->dropColumn('safety_referent_phone_'.$i);
                $table->dropColumn('safety_referent_document_'.$i);
            }
            
            for ($i = 1; $i <= 3; $i++) {
                $table->dropColumn('security_correspondent_name_'.$i);
                $table->dropColumn('security_correspondent_email_'.$i);
                $table->dropColumn('security_correspondent_phone_'.$i);
                $table->dropColumn('security_correspondent_document_'.$i);
            }
            
            $table->dropColumn('kbis_document');
            
            $table->dropColumn('hr_contact_name');
            $table->dropColumn('hr_contact_email');
            $table->dropColumn('hr_contact_phone');
        });
    }
};
