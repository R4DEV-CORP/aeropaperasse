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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_student')->default(false);
        });
        
        DB::table('users')
            ->where('role', 'formation')
            ->update([
                'role' => 'client',
                'is_student' => true
            ]);
        
        DB::table('users')
            ->whereIn('role', ['admin', 'sadmin'])
            ->update(['is_student' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_student');
        });
    }
};
