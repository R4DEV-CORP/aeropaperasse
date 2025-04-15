<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('safety_document')->nullable();
            $table->string('security_document')->nullable();
            
            $table->renameColumn('security_correspondent_name_1', 'security_correspondent_name');
            $table->renameColumn('security_correspondent_email_1', 'security_correspondent_email');
            $table->renameColumn('security_correspondent_phone_1', 'security_correspondent_phone');
        });
        
        DB::statement("UPDATE clients SET safety_document = safety_referent_document_1 WHERE safety_referent_document_1 IS NOT NULL");
        DB::statement("UPDATE clients SET security_document = security_correspondent_document_1 WHERE security_correspondent_document_1 IS NOT NULL");
        
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('safety_referent_document_1');
            $table->dropColumn('safety_referent_document_2');
            $table->dropColumn('safety_referent_document_3');
            $table->dropColumn('security_correspondent_document_1');
            $table->dropColumn('security_correspondent_name_2');
            $table->dropColumn('security_correspondent_email_2');
            $table->dropColumn('security_correspondent_phone_2');
            $table->dropColumn('security_correspondent_document_2');
            $table->dropColumn('security_correspondent_name_3');
            $table->dropColumn('security_correspondent_email_3');
            $table->dropColumn('security_correspondent_phone_3');
            $table->dropColumn('security_correspondent_document_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('safety_referent_document_1')->nullable();
            $table->string('safety_referent_document_2')->nullable();
            $table->string('safety_referent_document_3')->nullable();
            
            $table->renameColumn('security_correspondent_name', 'security_correspondent_name_1');
            $table->renameColumn('security_correspondent_email', 'security_correspondent_email_1');
            $table->renameColumn('security_correspondent_phone', 'security_correspondent_phone_1');
            
            $table->string('security_correspondent_document_1')->nullable();
            $table->string('security_correspondent_name_2')->nullable();
            $table->string('security_correspondent_email_2')->nullable();
            $table->string('security_correspondent_phone_2')->nullable();
            $table->string('security_correspondent_document_2')->nullable();
            $table->string('security_correspondent_name_3')->nullable();
            $table->string('security_correspondent_email_3')->nullable();
            $table->string('security_correspondent_phone_3')->nullable();
            $table->string('security_correspondent_document_3')->nullable();

            $table->dropColumn('safety_document');
            $table->dropColumn('security_document');
        });
        
        DB::statement("UPDATE clients SET safety_referent_document_1 = safety_document WHERE safety_document IS NOT NULL");
        DB::statement("UPDATE clients SET security_correspondent_document_1 = security_document WHERE security_document IS NOT NULL");
    }
};
