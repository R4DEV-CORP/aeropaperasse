<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'accès aux formations devient une donnée **par tenant** : un même utilisateur
     * peut avoir le droit dans le tenant A et pas dans le tenant B. On porte le flag
     * sur la pivot `tenant_user`. Le snapshot du `users.can_access_formation` au
     * moment de la bascule alimente chaque ligne pivot existante — la colonne sur
     * `users` reste en place mais devient dépréciée (cf. Q-CLIENT pour le pattern).
     */
    public function up(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->boolean('can_access_formation')->default(false)->after('client_id');
        });

        // Backfill : copie du flag global sur chaque pivot existante.
        // `tenant_user` et `users` sont tous deux en central → JOIN intra-DB possible.
        DB::connection('central')->statement('
            UPDATE tenant_user tu
            INNER JOIN users u ON u.id = tu.user_id
            SET tu.can_access_formation = u.can_access_formation
        ');
    }

    public function down(): void
    {
        Schema::table('tenant_user', function (Blueprint $table) {
            $table->dropColumn('can_access_formation');
        });
    }
};
