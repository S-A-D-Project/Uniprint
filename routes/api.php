<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Chat API Routes with enhanced security
Route::middleware(['auth', \App\Http\Middleware\CheckChatAccess::class])->prefix('chat')->group(function () {
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/conversations', [ChatController::class, 'getOrCreateConversation']);
    Route::get('/conversations/{conversationId}', [ChatController::class, 'getConversation']);
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::post('/messages/read', [ChatController::class, 'markAsRead']);
    Route::post('/typing', [ChatController::class, 'typing']);
    Route::post('/online-status', [ChatController::class, 'updateOnlineStatus']);
    Route::post('/online-status/check', [ChatController::class, 'getOnlineStatus']);
    Route::get('/available-businesses', [ChatController::class, 'getAvailableBusinesses']);
    
    // Pusher authentication endpoint
    Route::post('/pusher/auth', [ChatController::class, 'pusherAuth']);
    
    // Cleanup and health check endpoints
    Route::post('/cleanup', [ChatController::class, 'cleanup']);
    Route::get('/health', [ChatController::class, 'healthCheck']);
});
