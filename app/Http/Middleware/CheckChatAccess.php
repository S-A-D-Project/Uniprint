<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

class CheckChatAccess
{
    /**
     * Handle an incoming request to verify chat access permissions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to access the chat system.'
            ], 401);
        }

        $user = Auth::user();
        
        // Get conversation ID from route or request
        $conversationId = $request->route('conversationId') ?? $request->input('conversation_id');
        
        // If a specific conversation is being accessed, verify participation
        if ($conversationId) {
            $conversation = Conversation::find($conversationId);
            
            if (!$conversation) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'Conversation not found.'
                ], 404);
            }
            
            // Verify user is a participant in the conversation
            if ($conversation->customer_id !== $user->user_id && 
                $conversation->business_id !== $user->user_id) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have access to this conversation.'
                ], 403);
            }
            
            // Add conversation to request for later use
            $request->merge(['verified_conversation' => $conversation]);
        }
        
        // Check role-specific permissions
        $roleType = $user->role_type;
        
        // Only customers and business users can access chat
        if (!in_array($roleType, ['customer', 'business_user'])) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Your account type does not have chat access.'
            ], 403);
        }
        
        return $next($request);
    }
}
