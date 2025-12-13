<?php

namespace App\Services;

use Pusher\Pusher;
use Illuminate\Support\Facades\Log;

class PusherService
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );
    }

    /**
     * Broadcast a new message to a conversation channel
     */
    public function broadcastMessage($conversationId, $message)
    {
        try {
            $channel = "conversation.{$conversationId}";
            $event = 'new-message';
            
            $this->pusher->trigger($channel, $event, [
                'message_id' => $message->message_id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name ?? 'Unknown',
                'message_text' => $message->message_text,
                'message_type' => $message->message_type,
                'created_at' => $message->created_at->toISOString(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Pusher broadcast error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast typing indicator
     */
    public function broadcastTyping($conversationId, $userId, $userName, $isTyping)
    {
        try {
            $channel = "conversation.{$conversationId}";
            $event = 'user-typing';
            
            $this->pusher->trigger($channel, $event, [
                'user_id' => $userId,
                'user_name' => $userName,
                'is_typing' => $isTyping,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Pusher typing broadcast error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast online status change
     */
    public function broadcastOnlineStatus($userId, $status)
    {
        try {
            $channel = "user.{$userId}";
            $event = 'status-changed';
            
            $this->pusher->trigger($channel, $event, [
                'user_id' => $userId,
                'status' => $status,
                'timestamp' => now()->toISOString(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Pusher status broadcast error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast message read receipt
     */
    public function broadcastMessageRead($conversationId, $messageIds, $userId)
    {
        try {
            $channel = "conversation.{$conversationId}";
            $event = 'messages-read';
            
            $this->pusher->trigger($channel, $event, [
                'message_ids' => $messageIds,
                'read_by' => $userId,
                'read_at' => now()->toISOString(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Pusher read receipt error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Authorize a channel for a user with enhanced presence support
     */
    public function authorizeChannel($socketId, $channelName, $userId, $userData = [])
    {
        try {
            // For private channels
            if (strpos($channelName, 'private-') === 0) {
                return $this->pusher->authorizeChannel($channelName, $socketId);
            }

            // For presence channels with enhanced user data
            if (strpos($channelName, 'presence-') === 0) {
                $user = \App\Models\User::find($userId);
                
                $presenceData = array_merge([
                    'user_id' => $userId,
                    'name' => $user ? $user->name : 'Unknown',
                    'role_type' => $user ? $user->role_type : 'unknown',
                    'online_at' => now()->toISOString(),
                ], $userData);
                
                return $this->pusher->authorizePresenceChannel(
                    $channelName, 
                    $socketId, 
                    $userId, 
                    $presenceData
                );
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Pusher authorization error: ' . $e->getMessage(), [
                'channel' => $channelName,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Broadcast presence member added event
     */
    public function broadcastPresence($conversationId, $userId, $action = 'joined')
    {
        try {
            $channel = "presence-conversation.{$conversationId}";
            $event = 'presence-updated';
            
            $user = \App\Models\User::find($userId);
            
            $this->pusher->trigger($channel, $event, [
                'user_id' => $userId,
                'name' => $user ? $user->name : 'Unknown',
                'action' => $action,
                'timestamp' => now()->toISOString(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Pusher presence broadcast error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get channel info for monitoring
     */
    public function getChannelInfo($channelName)
    {
        try {
            return $this->pusher->get_info($channelName);
        } catch (\Exception $e) {
            Log::error('Pusher get channel info error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trigger batch events efficiently
     */
    public function triggerBatch(array $events)
    {
        try {
            $this->pusher->triggerBatch($events);
            return true;
        } catch (\Exception $e) {
            Log::error('Pusher batch trigger error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect/cleanup for a specific user
     */
    public function terminateUserPresence($userId)
    {
        try {
            // Update online status to offline
            \App\Models\OnlineUser::where('user_id', $userId)
                ->update([
                    'status' => 'offline',
                    'last_seen_at' => now()
                ]);

            Log::info('User presence terminated', ['user_id' => $userId]);
            return true;
        } catch (\Exception $e) {
            Log::error('Pusher terminate presence error: ' . $e->getMessage());
            return false;
        }
    }
}
