<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // Create conversations table
        Schema::create('conversations', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('conversation_id')->primary();
            $table->uuid('customer_id');
            $table->uuid('business_id');
            $table->string('subject')->nullable();
            $statusCol = $isSqlite
                ? $table->string('status')
                : $table->enum('status', ['active', 'closed', 'archived']);
            $statusCol->default('active');

            $initiatedByCol = $isSqlite
                ? $table->string('initiated_by')
                : $table->enum('initiated_by', ['customer', 'business']);
            $initiatedByCol->default('customer');
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
        Schema::create('chat_messages', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('message_id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id');
            $table->text('message_text');
            $typeCol = $isSqlite
                ? $table->string('message_type')
                : $table->enum('message_type', ['text', 'image', 'file', 'system']);
            $typeCol->default('text');
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
        Schema::create('online_users', function (Blueprint $table) use ($isSqlite) {
            $table->uuid('user_id')->primary();
            $table->timestamp('last_seen_at');
            $statusCol = $isSqlite
                ? $table->string('status')
                : $table->enum('status', ['online', 'away', 'offline']);
            $statusCol->default('online');
            
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
