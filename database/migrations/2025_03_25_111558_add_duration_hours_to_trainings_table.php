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
        Schema::table('trainings', function (Blueprint $table) {
            $table->decimal('duration_hours', 5, 2)->nullable()->after('short_title');
            $table->date('start_date')->nullable()->after('validity_duration');
            $table->date('expiry_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['duration_hours', 'start_date', 'expiry_date']);
        });
    }
};
