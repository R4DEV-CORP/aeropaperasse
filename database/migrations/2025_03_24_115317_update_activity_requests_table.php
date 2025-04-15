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
        Schema::table('activity_requests', function (Blueprint $table) {
            // Ajouter user_id
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Ajouter draft_at
            $table->timestamp('draft_at')->nullable()->after('status');

            // Ajouter previous_status
            $table->string('previous_status')->nullable()->after('status');

            // Ajouter created_by
            $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Modifier la colonne status pour inclure 'draft'
        DB::statement("ALTER TABLE activity_requests MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['created_by']);

            $table->dropColumn(['user_id', 'draft_at', 'previous_status', 'created_by']);
        });

        // Restaurer le statut original
        DB::statement("ALTER TABLE activity_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
