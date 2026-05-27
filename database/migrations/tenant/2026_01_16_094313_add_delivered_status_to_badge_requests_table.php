<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour ajouter 'delivered'
        DB::statement("ALTER TABLE `badge_requests` MODIFY COLUMN `status` ENUM('draft','pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery','delivered','terminated') NOT NULL DEFAULT 'pending_rem'");

        // Ajouter les colonnes pour la remise
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('ready_for_delivery_at');
            $table->string('delivery_photo')->nullable()->after('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les colonnes
        Schema::table('badge_requests', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'delivery_photo']);
        });

        // Restaurer l'enum sans 'delivered'
        DB::statement("ALTER TABLE `badge_requests` MODIFY COLUMN `status` ENUM('draft','pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery','terminated') NOT NULL DEFAULT 'pending_rem'");
    }
};
