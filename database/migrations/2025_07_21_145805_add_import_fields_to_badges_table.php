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
        Schema::table('badges', function (Blueprint $table) {
            // Informations du détenteur pour les badges importés
            $table->string('holder_nom')->nullable()->after('badge_request_id');
            $table->string('holder_prenom')->nullable()->after('holder_nom');
            $table->string('holder_email')->nullable()->after('holder_prenom');
            $table->string('holder_telephone')->nullable()->after('holder_email');
            $table->string('holder_client')->nullable()->after('holder_telephone');

            // Informations de traçabilité
            $table->string('external_request_number')->nullable()->after('holder_client');
            $table->date('request_date')->nullable()->after('external_request_number');
            $table->string('import_source')->nullable()->after('request_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn([
                'holder_nom',
                'holder_prenom',
                'holder_email',
                'holder_telephone',
                'holder_client',
                'external_request_number',
                'request_date',
                'import_source'
            ]);
        });
    }
};
