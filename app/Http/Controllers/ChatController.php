<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\OnlineUser;
use App\Models\User;
use App\Services\PusherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected $pusherService;

    public function __construct(PusherService $pusherService)
    {
        $this->pusherService = $pusherService;
    }

    private function getUserRoleType(string $userId): ?string
    {
        $roleRow = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->select('role_types.user_role_type')
            ->first();

        return $roleRow?->user_role_type;
    }

    private function getUsersByRoleType(string $roleType, array $excludeUserIds = [])
    {
        $query = DB::table('users')
            ->join('roles', 'users.user_id', '=', 'roles.user_id')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('role_types.user_role_type', $roleType)
            ->select('users.user_id', 'users.name', 'users.email', 'users.created_at')
            ->orderBy('users.name');

        if (!empty($excludeUserIds)) {
            $query->whereNotIn('users.user_id', $excludeUserIds);
        }

        return $query->get();
    }

    /**
     * Display chat interface
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all conversations for the current user
        $conversations = Conversation::where('customer_id', $user->user_id)
            ->orWhere('business_id', $user->user_id)
            ->with(['customer', 'business', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Get available businesses if user is a customer
        $availableBusinesses = collect();
        if ($this->getUserRoleType($user->user_id) === 'customer') {
            $existingBusinessIds = $conversations->pluck('business_id')->toArray();
            $availableBusinesses = $this->getUsersByRoleType('business_user', $existingBusinessIds);
        }
        
        return view('chat.index', compact('conversations', 'availableBusinesses'));
    }

    /**
     * Direct chat between Sarah Customer and Business User (No Database)
     */
    public function directChat()
    {
        $user = Auth::user();
        
        // Simple direct chat without database queries - just use Pusher
        // Define static user data for the chat
        $currentUserData = [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'role' => 'current_user'
        ];
        
        // Static other user data (will be determined by frontend)
        $otherUserData = [
            'user_id' => 'other_user',
            'name' => 'Chat Partner',
            'role' => 'other_user'
        ];
        
        // Static conversation ID for direct chat
        $conversationId = 'sarah-business-direct-chat';
        
        return view('chat.direct-simple', compact('currentUserData', 'otherUserData', 'conversationId'));
    }

    /**
     * Get or create conversation between customer and business
     * Only customers can initiate conversations
     */
    public function getOrCreateConversation(Request $request)
    {
        $request->validate([
            'business_id' => 'required|uuid|exists:users,user_id'
        ]);

        $currentUser = Auth::user();
        $businessId = $request->business_id;
        
        // Only customers can initiate conversations
        if ($this->getUserRoleType($currentUser->user_id) !== 'customer') {
            return response()->json([
                'error' => 'Only customers can initiate conversations',
                'message' => 'Chat conversations must be started by customers.'
            ], 403);
        }

        // Verify the target user is a business
        $business = User::findOrFail($businessId);
        if ($this->getUserRoleType($business->user_id) !== 'business_user') {
            return response()->json([
                'error' => 'Invalid business user',
                'message' => 'You can only start conversations with business representatives.'
            ], 400);
        }

        $customerId = $currentUser->user_id;

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'customer_id' => $customerId,
                'business_id' => $businessId,
            ],
            [
                'status' => 'active',
                'initiated_by' => 'customer',
                'initiated_at' => now()
            ]
        );

        $conversation->load(['customer', 'business', 'messages.sender']);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'message' => 'Conversation ready'
        ]);
    }

    /**
     * Get conversation details
     */
    public function getConversation($conversationId)
    {
        $conversation = Conversation::with(['customer', 'business'])
            ->findOrFail($conversationId);

        // Check if user is participant
        $userId = Auth::user()->user_id;
        if ($conversation->customer_id !== $userId && $conversation->business_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages($conversationId, Request $request)
    {
        // Validate input parameters
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0'
        ]);

        // Validate conversation ID format
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $conversationId)) {
            return response()->json(['error' => 'Invalid conversation ID format'], 400);
        }

        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is participant
        $userId = Auth::user()->user_id;
        if ($conversation->customer_id !== $userId && $conversation->business_id !== $userId) {
            return response()->json(['error' => 'Unauthorized access to conversation'], 403);
        }

        $limit = min((int)$request->get('limit', 50), 100); // Cap at 100
        $offset = max((int)$request->get('offset', 0), 0);   // Ensure non-negative

        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send a message (supports both database and direct chat)
     */
    public function sendMessage(Request $request)
    {
        // Check if this is the direct chat (no database)
        if ($request->input('conversation_id') === 'sarah-business-direct-chat') {
            $request->validate([
                'conversation_id' => 'required|string',
                'message_text' => 'required|string|max:1000',
                'message_type' => 'nullable|in:text,image,file,system',
                'sender_id' => 'required|string',
                'sender_name' => 'required|string',
                'message_id' => 'required|string',
                'timestamp' => 'required|string'
            ]);

            try {
                // Broadcast message via Pusher for direct chat
                $messageData = [
                    'message_id' => $request->message_id,
                    'sender_id' => $request->sender_id,
                    'sender_name' => $request->sender_name,
                    'message_text' => $request->message_text,
                    'timestamp' => $request->timestamp,
                    'conversation_id' => $request->conversation_id
                ];

                // Use Pusher to broadcast the message
                $pusher = new \Pusher\Pusher(
                    config('broadcasting.connections.pusher.key'),
                    config('broadcasting.connections.pusher.secret'),
                    config('broadcasting.connections.pusher.app_id'),
                    config('broadcasting.connections.pusher.options')
                );

                $channelName = 'direct-chat.' . $request->conversation_id;
                $pusher->trigger($channelName, 'new-message', $messageData);

                return response()->json([
                    'success' => true,
                    'message' => 'Message broadcasted successfully',
                    'data' => $messageData
                ]);

            } catch (\Exception $e) {
                \Log::error('Direct chat broadcast error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to broadcast message: ' . $e->getMessage()
                ], 500);
            }
        }

        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,conversation_id',
            'message_text' => 'required|string|max:1000',
            'message_type' => 'nullable|in:text,image,file,system',
        ]);

        // Original database-backed conversation logic
        $conversation = Conversation::findOrFail($request->conversation_id);
        $userId = Auth::user()->user_id;

        // Check if user is participant
        if ($conversation->customer_id !== $userId && $conversation->business_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = ChatMessage::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $userId,
            'message_text' => $request->message_text,
            'message_type' => $request->message_type ?? 'text',
        ]);

        // Update conversation's last message timestamp
        $conversation->update([
            'last_message_at' => now()
        ]);

        $message->load('sender');

        // Broadcast message via Pusher
        $this->pusherService->broadcastMessage($conversation->conversation_id, $message);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,conversation_id',
            'message_ids' => 'nullable|array',
            'message_ids.*' => 'uuid|exists:chat_messages,message_id'
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $userId = Auth::user()->user_id;

        // Check if user is participant
        if ($conversation->customer_id !== $userId && $conversation->business_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = ChatMessage::where('conversation_id', $request->conversation_id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false);

        if ($request->has('message_ids')) {
            $query->whereIn('message_id', $request->message_ids);
        }

        $messageIds = $query->pluck('message_id')->toArray();
        
        $updated = $query->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        // Broadcast read receipt via Pusher
        if ($updated > 0) {
            $this->pusherService->broadcastMessageRead(
                $request->conversation_id,
                $messageIds,
                $userId
            );
        }

        return response()->json([
            'success' => true,
            'updated_count' => $updated
        ]);
    }

    /**
     * Update user online status
     */
    public function updateOnlineStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,away,offline'
        ]);

        $userId = Auth::user()->user_id;
        
        OnlineUser::updateOrCreate(
            ['user_id' => $userId],
            [
                'status' => $request->status,
                'last_seen_at' => now()
            ]
        );

        // Broadcast online status via Pusher
        $this->pusherService->broadcastOnlineStatus($userId, $request->status);

        return response()->json(['success' => true]);
    }

    /**
     * Get online status of users
     */
    public function getOnlineStatus(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'uuid|exists:users,user_id'
        ]);

        $onlineUsers = OnlineUser::whereIn('user_id', $request->user_ids)
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->get()
            ->keyBy('user_id');

        $statuses = [];
        foreach ($request->user_ids as $userId) {
            $onlineUser = $onlineUsers->get($userId);
            $statuses[$userId] = [
                'online' => $onlineUser && $onlineUser->status === 'online',
                'last_seen' => $onlineUser ? $onlineUser->last_seen_at->toISOString() : null,
                'status' => $onlineUser ? $onlineUser->status : 'offline'
            ];
        }

        return response()->json([
            'success' => true,
            'statuses' => $statuses
        ]);
    }

    /**
     * Broadcast typing indicator
     */
    public function typing(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,conversation_id',
            'is_typing' => 'required|boolean'
        ]);

        $userId = Auth::user()->user_id;
        $userName = Auth::user()->name;

        // Broadcast typing indicator via Pusher
        $this->pusherService->broadcastTyping(
            $request->conversation_id,
            $userId,
            $userName,
            $request->is_typing
        );

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Get all conversations for current user
     */
    public function getConversations()
    {
        $user = Auth::user();
        $userId = $user->user_id;
        $currentRole = $this->getUserRoleType($userId);
        
        $conversations = Conversation::where('customer_id', $userId)
            ->orWhere('business_id', $userId)
            ->with(['customer', 'business', 'lastMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($userId, $user) {
                $otherParticipant = $conversation->getOtherParticipant($userId);
                return [
                    'conversation_id' => $conversation->conversation_id,
                    'participant' => [
                        'user_id' => $otherParticipant->user_id,
                        'name' => $otherParticipant->name,
                        'role' => $this->getUserRoleType($otherParticipant->user_id),
                    ],
                    'last_message' => $conversation->lastMessage,
                    'unread_count' => $conversation->unreadCount($userId),
                    'last_message_at' => $conversation->last_message_at,
                    'initiated_by' => $conversation->initiated_by ?? 'customer',
                    'can_initiate' => $this->getUserRoleType($userId) === 'customer'
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'user_role' => $currentRole,
            'can_initiate_chat' => $currentRole === 'customer'
        ]);
    }

    /**
     * Get available businesses for customers to chat with
     */
    public function getAvailableBusinesses()
    {
        $currentUser = Auth::user();
        
        // Only customers can view available businesses
        if ($this->getUserRoleType($currentUser->user_id) !== 'customer') {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Only customers can view available businesses.'
            ], 403);
        }

        // Get businesses that don't have existing conversations with this customer
        $existingBusinessIds = Conversation::where('customer_id', $currentUser->user_id)
            ->pluck('business_id')
            ->toArray();

        $availableBusinesses = $this->getUsersByRoleType('business_user', $existingBusinessIds);

        return response()->json([
            'success' => true,
            'businesses' => $availableBusinesses
        ]);
    }

    /**
     * Authenticate Pusher private/presence channels
     */
    public function pusherAuth(Request $request)
    {
        try {
            $socketId = $request->input('socket_id');
            $channelName = $request->input('channel_name');
            
            if (!$socketId || !$channelName) {
                return response()->json(['error' => 'Invalid request'], 400);
            }

            $user = Auth::user();
            
            // Verify user has access to the channel
            if (strpos($channelName, 'conversation.') === 0 || strpos($channelName, 'presence-conversation.') === 0) {
                // Extract conversation ID from channel name
                $conversationId = str_replace(['conversation.', 'presence-conversation.'], '', $channelName);
                $conversation = Conversation::find($conversationId);
                
                if (!$conversation) {
                    return response()->json(['error' => 'Conversation not found'], 404);
                }
                
                // Check if user is a participant
                if ($conversation->customer_id !== $user->user_id && 
                    $conversation->business_id !== $user->user_id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }

            // Authorize the channel with user data
            $userData = [
                'name' => $user->name,
                'role_type' => $this->getUserRoleType($user->user_id),
            ];
            
            $auth = $this->pusherService->authorizeChannel($socketId, $channelName, $user->user_id, $userData);
            
            if ($auth) {
                return response()->json(json_decode($auth));
            }
            
            return response()->json(['error' => 'Authorization failed'], 403);
            
        } catch (\Exception $e) {
            \Log::error('Pusher auth error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Cleanup Pusher resources when user leaves chat
     */
    public function cleanup(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'nullable|uuid|exists:conversations,conversation_id'
            ]);

            $userId = Auth::user()->user_id;
            
            // Update user status to offline
            OnlineUser::where('user_id', $userId)
                ->update([
                    'status' => 'offline',
                    'last_seen_at' => now()
                ]);
            
            // If specific conversation, broadcast presence left
            if ($request->has('conversation_id')) {
                $this->pusherService->broadcastPresence(
                    $request->conversation_id,
                    $userId,
                    'left'
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Cleanup completed'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Cleanup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed'
            ], 500);
        }
    }

    /**
     * Check Pusher connection health
     */
    public function healthCheck()
    {
        try {
            $channelInfo = $this->pusherService->getChannelInfo('test-channel');
            
            return response()->json([
                'success' => true,
                'pusher_available' => true,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'pusher_available' => false,
                'error' => 'Pusher connection failed'
            ], 503);
        }
    }
}
