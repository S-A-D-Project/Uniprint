<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enhance notifications system with preferences and better structure.
     */
    public function up(): void
    {
        // Check if notifications table exists, if not create it
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('notification_id')->primary();
                $table->uuid('user_id');
                $table->string('type', 50); // 'order_update', 'promotion', 'system', etc.
                $table->string('title', 255);
                $table->text('message');
                $table->json('data')->nullable(); // Additional data (order_id, etc.)
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['user_id']);
                $table->index(['type']);
                $table->index(['is_read']);
                $table->index(['created_at']);
                $table->index(['expires_at']);
                
                // Foreign key constraints
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } else {
            // Enhance existing notifications table
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'type')) {
                    $table->string('type', 50)->default('general')->after('user_id');
                }
                if (!Schema::hasColumn('notifications', 'title')) {
                    $table->string('title', 255)->nullable()->after('type');
                }
                if (!Schema::hasColumn('notifications', 'data')) {
                    $table->json('data')->nullable()->after('message');
                }
                if (!Schema::hasColumn('notifications', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('read_at');
                }
            });
        }

        // Notification preferences table
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('preference_id')->primary();
            $table->uuid('user_id');
            $table->string('type', 50); // notification type
            $table->boolean('email_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id']);
            $table->index(['type']);
            
            // Foreign key constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Unique constraint - one preference per user per type
            $table->unique(['user_id', 'type'], 'unique_user_notification_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        
        // Only drop notifications table if it was created by this migration
        // In production, you might want to keep existing notifications
        if (Schema::hasTable('notifications')) {
            // Remove added columns if they exist
            Schema::table('notifications', function (Blueprint $table) {
                $columns = ['type', 'title', 'data', 'expires_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('notifications', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
