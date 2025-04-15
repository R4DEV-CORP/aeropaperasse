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
        Schema::table('badge_requests', function (Blueprint $table) {
            Schema::table('badge_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('badge_requests', 'client_id')) {
                    $table->unsignedBigInteger('client_id')->nullable();
                    $table->foreign('client_id')->references('id')->on('clients');
                }
                
                if (!Schema::hasColumn('badge_requests', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->foreign('created_by')->references('id')->on('users');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_requests', function (Blueprint $table) {
            if (Schema::hasColumn('badge_requests', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
            
            if (Schema::hasColumn('badge_requests', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
