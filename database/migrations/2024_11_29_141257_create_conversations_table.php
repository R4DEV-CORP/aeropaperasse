<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    // Table conversations
    Schema::create('conversations', function (Blueprint $table) {
        $table->id();
        $table->string('object')->nullable();
        $table->string('status')->nullable();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
        $table->softDeletes();
    });

    // Table messages
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->text('content');
        $table->boolean('is_read')->default(false);
        $table->json('attachments')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
    
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('attachments');
    }
};
