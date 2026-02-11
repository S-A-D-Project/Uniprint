<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\ServiceMarketplaceApiController;
use App\Http\Controllers\Api\CustomerDashboardApiController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckChatAccess;

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

// NOTE: Sanctum is not installed yet in composer.json.
// These API routes are migrated from routes/web.php, but are temporarily wrapped in 'web'
// middleware so existing session-authenticated fetch calls keep working.
// Once Sanctum is installed/configured, swap middleware to auth:sanctum and remove 'web'.

Route::post('/auth/token', [AuthTokenController::class, 'issueToken'])->name('api.auth.token');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthTokenController::class, 'revokeCurrentToken'])->name('api.auth.logout');

    // Service Marketplace API routes
    Route::prefix('marketplace')
        ->middleware([CheckRole::class . ':customer'])
        ->name('api.marketplace.')
        ->group(function () {
            Route::get('/services', [ServiceMarketplaceApiController::class, 'getServices']);
            Route::get('/search-suggestions', [ServiceMarketplaceApiController::class, 'getSearchSuggestions']);
            Route::get('/service/{serviceId}', [ServiceMarketplaceApiController::class, 'getServiceDetails']);
            Route::post('/toggle-favorite', [ServiceMarketplaceApiController::class, 'toggleFavorite']);
            Route::get('/categories', [ServiceMarketplaceApiController::class, 'getCategories']);
            Route::get('/enterprises', [ServiceMarketplaceApiController::class, 'getEnterprises']);
            Route::get('/locations', [ServiceMarketplaceApiController::class, 'getLocations']);
        });

    // Customer API routes
    Route::prefix('customer')
        ->middleware([CheckRole::class . ':customer'])
        ->name('api.customer.')
        ->group(function () {
            Route::get('/services', [CustomerDashboardApiController::class, 'getServices']);
            Route::get('/orders', [CustomerDashboardApiController::class, 'getOrders']);
            Route::get('/payments', [CustomerDashboardApiController::class, 'getPaymentHistory']);
            Route::post('/profile', [CustomerDashboardApiController::class, 'updateProfile']);
            Route::get('/stats', [CustomerDashboardApiController::class, 'getDashboardStats']);
            Route::get('/saved-services', [CustomerDashboardApiController::class, 'getSavedServices']);
        });

    // Chat API routes
    Route::prefix('chat')->group(function () {
        Route::post('/enterprise-owner', [ChatController::class, 'resolveEnterpriseOwner']);
    });

    Route::prefix('chat')
        ->middleware([CheckChatAccess::class])
        ->group(function () {
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
            Route::post('/pusher/auth', [ChatController::class, 'pusherAuth']);
            Route::post('/cleanup', [ChatController::class, 'cleanup']);
            Route::get('/health', [ChatController::class, 'healthCheck']);
        });

    // Pricing API routes
    Route::post('/pricing/calculate', [PricingController::class, 'calculatePrice'])->name('api.pricing.calculate');
});
