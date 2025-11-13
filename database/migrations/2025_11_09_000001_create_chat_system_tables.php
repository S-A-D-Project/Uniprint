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
        // Create conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('conversation_id')->primary();
            $table->uuid('customer_id');
            $table->uuid('business_id');
            $table->string('subject')->nullable();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->enum('initiated_by', ['customer', 'business'])->default('customer');
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('business_id')->references('user_id')->on('users')->onDelete('cascade');
            
            $table->index(['customer_id', 'business_id']);
            $table->index('last_message_at');
            $table->index('initiated_by');
        });

        // Create chat messages table
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('message_id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id');
            $table->text('message_text');
            $table->enum('message_type', ['text', 'image', 'file', 'system'])->default('text');
            $table->string('attachment_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('conversation_id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('user_id')->on('users')->onDelete('cascade');
            
            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
        });

        // Create online users tracking table
        Schema::create('online_users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->timestamp('last_seen_at');
            $table->enum('status', ['online', 'away', 'offline'])->default('online');
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Create typing indicators table
        Schema::create('typing_indicators', function (Blueprint $table) {
            $table->uuid('conversation_id');
            $table->uuid('user_id');
            $table->timestamp('started_at');
            
            $table->primary(['conversation_id', 'user_id']);
            $table->foreign('conversation_id')->references('conversation_id')->on('conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typing_indicators');
        Schema::dropIfExists('online_users');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversations');
    }
};
