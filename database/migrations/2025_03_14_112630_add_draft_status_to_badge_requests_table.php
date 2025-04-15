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
        DB::statement("ALTER TABLE badge_requests MODIFY COLUMN status ENUM('draft', 'pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery') NOT NULL DEFAULT 'pending_rem'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE badge_requests MODIFY COLUMN status ENUM('pending_rem','rejected_rem','pending_adp','approved_adp','rejected_adp','pending_fabrication','ready_for_delivery') NOT NULL DEFAULT 'pending_rem'");
    }
};
