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
        Schema::create('discussion_read_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discussion_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_read_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'discussion_id']);
        });
        
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
        });
        
        Schema::dropIfExists('discussion_read_status');
    }
};
