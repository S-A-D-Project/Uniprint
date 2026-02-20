<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\ModernCustomerDashboardController;
use App\Http\Controllers\ServiceMarketplaceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SavedServiceController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\BusinessOnboardingController;
use App\Http\Controllers\BusinessVerificationController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\SystemFeedbackController;
use App\Http\Middleware\EnsureBusinessVerified;
use App\Http\Middleware\TwoFactorVerify;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Configure rate limiting
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(3)->by($request->ip() . '|' . $request->input('username'));
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/terms', 'public.terms')->name('terms');
Route::get('/enterprises', [HomeController::class, 'enterprises'])->name('enterprises.index');
Route::get('/enterprises/{id}', [HomeController::class, 'showEnterprise'])->whereUuid('id')->name('enterprises.show');
Route::get('/services/{id}', [HomeController::class, 'showService'])->whereUuid('id')->name('services.show');

// Authentication routes
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Social Authentication routes (outside throttle to ensure availability)
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback.alias');
Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');

// Admin entrypoint
Route::get('/admin', function () {
    $userId = session('user_id');
    if (!$userId) {
        return redirect()->route('admin.login');
    }

    $role = \Illuminate\Support\Facades\DB::table('roles')
        ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
        ->where('roles.user_id', $userId)
        ->first();

    $roleType = $role?->user_role_type;
    if ($roleType === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($roleType === 'business_user') {
        $hasEnterprise = \Illuminate\Support\Facades\DB::table('staff')->where('user_id', $userId)->exists();
        if (!$hasEnterprise) {
            return redirect()->route('business.onboarding');
        }
        return redirect()->route('business.dashboard');
    }

    return redirect()->route('customer.dashboard');
});


// Saved Services routes
Route::middleware([\App\Http\Middleware\CheckAuth::class, TwoFactorVerify::class])->group(function () {
    // Security settings (Email 2FA toggle)
    Route::get('/security', [TwoFactorController::class, 'securitySettings'])->name('security.settings');
    Route::post('/security/two-factor/enable', [TwoFactorController::class, 'startEnableEmail2fa'])->name('security.two-factor.enable');
    Route::post('/security/two-factor/confirm', [TwoFactorController::class, 'confirmEnableEmail2fa'])->name('security.two-factor.confirm');
    Route::post('/security/two-factor/resend', [TwoFactorController::class, 'resendEnableEmail2fa'])->name('security.two-factor.resend');
    Route::post('/security/two-factor/disable', [TwoFactorController::class, 'disableEmail2fa'])->name('security.two-factor.disable');

    // Email 2FA verification
    Route::get('/verify', [TwoFactorController::class, 'showVerify'])->name('two-factor.verify');
    Route::post('/verify', [TwoFactorController::class, 'submitVerify'])->name('two-factor.verify.submit');
    Route::post('/verify/resend', [TwoFactorController::class, 'resendVerifyCode'])->name('two-factor.verify.resend');

    // Two-factor challenge (must be reachable for logged-in users who haven't completed 2FA yet)
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.totp.verify');
    Route::post('/two-factor/send-email-code', [TwoFactorController::class, 'sendEmailCode'])->name('two-factor.email.send');
    Route::post('/two-factor/verify-email-code', [TwoFactorController::class, 'verifyEmailCode'])->name('two-factor.email.verify');

    Route::get('/saved-services', [\App\Http\Controllers\SavedServiceController::class, 'index'])->name('saved-services.index');
    Route::post('/saved-services/save', [\App\Http\Controllers\SavedServiceController::class, 'save'])->name('saved-services.save');
    Route::post('/checkout/from-service', [\App\Http\Controllers\CheckoutController::class, 'fromService'])->name('checkout.from-service');
    Route::patch('/saved-services/{savedServiceId}', [\App\Http\Controllers\SavedServiceController::class, 'update'])->name('saved-services.update');
    Route::delete('/saved-services/{savedServiceId}', [\App\Http\Controllers\SavedServiceController::class, 'remove'])->name('saved-services.remove');
    Route::post('/saved-services/clear', [\App\Http\Controllers\SavedServiceController::class, 'clear'])->name('saved-services.clear');
    Route::get('/saved-services/count', [\App\Http\Controllers\SavedServiceController::class, 'getCount'])->name('saved-services.count');
    Route::post('/saved-services/selection', [\App\Http\Controllers\SavedServiceController::class, 'setSelection'])->name('saved-services.selection.set');
    Route::delete('/saved-services/selection', [\App\Http\Controllers\SavedServiceController::class, 'clearSelection'])->name('saved-services.selection.clear');
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/connect/facebook', [ProfileController::class, 'redirectToFacebookConnect'])->name('profile.connect-facebook');
    Route::get('/profile/connect/facebook/callback', [ProfileController::class, 'handleFacebookConnectCallback'])->name('profile.connect-facebook.callback');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/upload-picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-picture');
    Route::post('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    Route::get('/profile/notifications', [ProfileController::class, 'getNotifications'])->name('profile.notifications');
    Route::post('/profile/notifications/{id}/read', [ProfileController::class, 'markNotificationRead'])->name('profile.notifications.read');

    // Two-factor settings
    Route::get('/profile/two-factor', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/profile/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/profile/two-factor/methods', [TwoFactorController::class, 'updateMethods'])->name('two-factor.methods.update');

    // Payment account settings
    Route::get('/profile/paypal/connect', [ProfileController::class, 'redirectToPayPalConnect'])->name('profile.paypal.connect');
    Route::get('/profile/paypal/callback', [ProfileController::class, 'handlePayPalConnectCallback'])->name('profile.paypal.callback');
    Route::post('/profile/paypal/disconnect', [ProfileController::class, 'disconnectPayPal'])->name('profile.paypal.disconnect');
    
    // Checkout routes
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/paypal/create-order', [\App\Http\Controllers\CheckoutController::class, 'paypalCreateOrder'])->name('checkout.paypal.create-order');
    Route::post('/checkout/paypal/capture-order', [\App\Http\Controllers\CheckoutController::class, 'paypalCaptureOrder'])->name('checkout.paypal.capture-order');
    Route::post('/checkout/apply-discount', [\App\Http\Controllers\CheckoutController::class, 'applyDiscountCode'])->name('checkout.apply-discount');
});

// Business onboarding routes (must be authenticated and business_user role)
Route::prefix('business')->middleware([\App\Http\Middleware\CheckAuth::class, \App\Http\Middleware\CheckRole::class.':business_user'])->name('business.')->group(function () {
    Route::get('/onboarding', [BusinessOnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [BusinessOnboardingController::class, 'store'])->name('onboarding.store');

    Route::get('/verification', [BusinessVerificationController::class, 'show'])->name('verification');
    Route::post('/verification', [BusinessVerificationController::class, 'store'])->name('verification.store');
});

// Admin routes
Route::prefix('admin')->middleware([\App\Http\Middleware\CheckAuth::class, \App\Http\Middleware\CheckRole::class.':admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}', [AdminController::class, 'userDetails'])->whereUuid('id')->name('users.details');
    Route::post('/users/{id}/toggle-active', [AdminController::class, 'toggleUserActive'])->whereUuid('id')->name('users.toggle-active');
    Route::post('/users/{id}/disable-email-2fa', [AdminController::class, 'disableUserEmail2fa'])->whereUuid('id')->name('users.disable-email-2fa');
    Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->whereUuid('id')->name('users.delete');
    Route::get('/enterprises', [AdminController::class, 'enterprises'])->name('enterprises');
    Route::get('/enterprises/{id}', [AdminController::class, 'enterpriseDetails'])->whereUuid('id')->name('enterprises.details');
    Route::post('/enterprises/{id}/verify', [AdminController::class, 'verifyEnterprise'])->whereUuid('id')->name('enterprises.verify');
    Route::post('/enterprises/{id}/toggle-active', [AdminController::class, 'toggleEnterpriseActive'])->whereUuid('id')->name('enterprises.toggle-active');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AdminController::class, 'orderDetails'])->whereUuid('id')->name('orders.details');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->whereUuid('id')->name('orders.update-status');
    Route::get('/services', [AdminController::class, 'services'])->name('services');
    Route::get('/services/{id}', [AdminController::class, 'serviceDetails'])->whereUuid('id')->name('services.details');
    Route::post('/services/{id}/toggle-active', [AdminController::class, 'toggleServiceActive'])->whereUuid('id')->name('services.toggle-active');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/user-reports', [AdminController::class, 'userReports'])->name('user-reports');
    Route::post('/user-reports/{id}/resolve', [AdminController::class, 'resolveUserReport'])->whereUuid('id')->name('user-reports.resolve');

    Route::get('/system-feedback', [AdminController::class, 'systemFeedback'])->name('system-feedback');
    Route::post('/system-feedback/{id}/review', [AdminController::class, 'markSystemFeedbackReviewed'])->whereUuid('id')->name('system-feedback.review');
    Route::post('/system-feedback/{id}/address', [AdminController::class, 'markSystemFeedbackAddressed'])->whereUuid('id')->name('system-feedback.address');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    
    // Real-time API endpoints
    Route::get('/api/dashboard-stats', [AdminController::class, 'getDashboardStats'])->name('api.dashboard-stats');
    Route::get('/api/enterprise-stats', [AdminController::class, 'getEnterpriseStats'])->name('api.enterprise-stats');
    
    // System Management
    Route::get('/settings', [\App\Http\Controllers\Admin\SystemController::class, 'settings'])->name('settings');
    Route::post('/settings/branding', [\App\Http\Controllers\Admin\SystemController::class, 'updateBranding'])->name('settings.branding');
    Route::post('/settings/order-auto-complete', [\App\Http\Controllers\Admin\SystemController::class, 'updateOrderAutoComplete'])->name('settings.order-auto-complete');
    Route::post('/settings/order-overdue-cancel-days', [\App\Http\Controllers\Admin\SystemController::class, 'updateOrderOverdueCancelDays'])->name('settings.order-overdue-cancel-days');
    Route::post('/settings/tax-rate', [\App\Http\Controllers\Admin\SystemController::class, 'updateTaxRate'])->name('settings.tax-rate');
    Route::post('/backup/create', [\App\Http\Controllers\Admin\SystemController::class, 'createBackup'])->name('backup.create');
    Route::get('/backup/download/{filename}', [\App\Http\Controllers\Admin\SystemController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/delete/{filename}', [\App\Http\Controllers\Admin\SystemController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/database/reset', [\App\Http\Controllers\Admin\SystemController::class, 'resetDatabase'])->name('database.reset');
    Route::post('/cache/clear', [\App\Http\Controllers\Admin\SystemController::class, 'clearCache'])->name('cache.clear');
    Route::post('/optimize', [\App\Http\Controllers\Admin\SystemController::class, 'optimize'])->name('optimize');
});

// Business routes
Route::prefix('business')->middleware([\App\Http\Middleware\CheckAuth::class, TwoFactorVerify::class, EnsureBusinessVerified::class])->name('business.')->group(function () {
    Route::middleware([\App\Http\Middleware\CheckRole::class.':business_user|admin'])->group(function () {
        Route::get('/services/create', [BusinessController::class, 'createService'])->name('services.create');
        Route::post('/services', [BusinessController::class, 'storeService'])->name('services.store');
        Route::get('/services/{id}/edit', [BusinessController::class, 'editService'])->whereUuid('id')->name('services.edit');
        Route::put('/services/{id}', [BusinessController::class, 'updateService'])->whereUuid('id')->name('services.update');

        Route::post('/services/{serviceId}/images/{imageId}/primary', [BusinessController::class, 'setServicePrimaryImage'])
            ->whereUuid('serviceId')
            ->whereUuid('imageId')
            ->name('services.images.primary');
    });

    Route::middleware([\App\Http\Middleware\CheckRole::class.':business_user'])->group(function () {
        Route::get('/dashboard', [BusinessController::class, 'dashboard'])->name('dashboard');

        // Settings
        Route::get('/settings', [BusinessController::class, 'settings'])->name('settings');
        Route::put('/settings/account', [BusinessController::class, 'updateAccount'])->name('settings.account.update');
        Route::put('/settings/enterprise', [BusinessController::class, 'updateEnterprise'])->name('settings.enterprise.update');
        
        // Order Management
        Route::get('/orders', [BusinessController::class, 'orders'])->name('orders.index');
        Route::get('/orders/walk-in/create', [BusinessController::class, 'createWalkInOrder'])->name('orders.walk-in.create');
        Route::post('/orders/walk-in', [BusinessController::class, 'storeWalkInOrder'])->name('orders.walk-in.store');
        Route::get('/orders/{id}', [BusinessController::class, 'orderDetails'])->whereUuid('id')->name('orders.details');
        Route::get('/orders/{id}/print', [BusinessController::class, 'printOrder'])->whereUuid('id')->name('orders.print');
        Route::post('/orders/{id}/confirm', [BusinessController::class, 'confirmOrder'])->whereUuid('id')->name('orders.confirm');
        Route::post('/orders/{id}/status', [BusinessController::class, 'updateOrderStatus'])->whereUuid('id')->name('orders.update-status');
        Route::post('/orders/{id}/downpayment-received', [BusinessController::class, 'markDownpaymentReceived'])->whereUuid('id')->name('orders.downpayment-received');
        Route::post('/orders/{id}/payment-confirm', [BusinessController::class, 'confirmPayment'])->whereUuid('id')->name('orders.payment.confirm');
        Route::post('/orders/{id}/extension-request', [BusinessController::class, 'requestOrderExtension'])->whereUuid('id')->name('orders.extension.request');

        // Notifications
        Route::get('/notifications', [BusinessController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{id}/read', [BusinessController::class, 'markNotificationRead'])->whereUuid('id')->name('notifications.read');
        
        // Service Management
        Route::get('/services', [BusinessController::class, 'services'])->name('services.index');
        Route::post('/services/{id}/upload-settings', [BusinessController::class, 'updateServiceUploadSettings'])->whereUuid('id')->name('services.upload-settings');
        Route::post('/services/{id}/toggle-status', [BusinessController::class, 'toggleServiceStatus'])->whereUuid('id')->name('services.toggle-status');
        Route::delete('/services/{id}', [BusinessController::class, 'deleteService'])->whereUuid('id')->name('services.delete');
        
        // Customization Management
        Route::get('/services/{serviceId}/customizations', [BusinessController::class, 'customizations'])->whereUuid('serviceId')->name('customizations.index');
        Route::post('/services/{serviceId}/customizations', [BusinessController::class, 'storeCustomization'])->whereUuid('serviceId')->name('customizations.store');
        Route::put('/services/{serviceId}/customizations/custom-size', [BusinessController::class, 'updateCustomSizeSettings'])->whereUuid('serviceId')->name('customizations.custom-size.update');
        Route::put('/services/{serviceId}/customizations/{optionId}', [BusinessController::class, 'updateCustomization'])->whereUuid('serviceId')->whereUuid('optionId')->name('customizations.update');
        Route::delete('/services/{serviceId}/customizations/{optionId}', [BusinessController::class, 'deleteCustomization'])->whereUuid('serviceId')->whereUuid('optionId')->name('customizations.delete');

        Route::post('/services/{serviceId}/custom-fields', [BusinessController::class, 'storeCustomField'])->whereUuid('serviceId')->name('custom-fields.store');
        Route::put('/services/{serviceId}/custom-fields/{fieldId}', [BusinessController::class, 'updateCustomField'])->whereUuid('serviceId')->whereUuid('fieldId')->name('custom-fields.update');
        Route::delete('/services/{serviceId}/custom-fields/{fieldId}', [BusinessController::class, 'deleteCustomField'])->whereUuid('serviceId')->whereUuid('fieldId')->name('custom-fields.delete');
        
        
        // Pricing Rules Management
        Route::get('/pricing-rules', [BusinessController::class, 'pricingRules'])->name('pricing.index');
        Route::get('/pricing-rules/create', [BusinessController::class, 'createPricingRule'])->name('pricing.create');
        Route::post('/pricing-rules', [BusinessController::class, 'storePricingRule'])->name('pricing.store');
        Route::get('/pricing-rules/{id}/edit', [BusinessController::class, 'editPricingRule'])->whereUuid('id')->name('pricing.edit');
        Route::put('/pricing-rules/{id}', [BusinessController::class, 'updatePricingRule'])->whereUuid('id')->name('pricing.update');
        Route::delete('/pricing-rules/{id}', [BusinessController::class, 'deletePricingRule'])->whereUuid('id')->name('pricing.delete');
        
        // Design File Management
        Route::post('/design-files/{fileId}/approve', [BusinessController::class, 'approveDesignFile'])->whereUuid('fileId')->name('design-files.approve');
        Route::post('/design-files/{fileId}/reject', [BusinessController::class, 'rejectDesignFile'])->whereUuid('fileId')->name('design-files.reject');
        
        // Chat Management
        Route::get('/chat', [BusinessController::class, 'chat'])->name('chat');
    });

    Route::middleware([\App\Http\Middleware\CheckRole::class.':business_user'])->group(function () {
        Route::get('/pending', function () {
            $userId = session('user_id');

            $enterprise = null;
            if ($userId && \Illuminate\Support\Facades\Schema::hasTable('enterprises') && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
                $enterprise = \Illuminate\Support\Facades\DB::table('enterprises')
                    ->where('owner_user_id', $userId)
                    ->first();
            }

            if (!$enterprise && $userId && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
                $enterpriseId = \Illuminate\Support\Facades\DB::table('staff')->where('user_id', $userId)->value('enterprise_id');
                if ($enterpriseId) {
                    $enterprise = \Illuminate\Support\Facades\DB::table('enterprises')->where('enterprise_id', $enterpriseId)->first();
                }
            }

            return view('business.pending', compact('enterprise'));
        })->name('pending');
    });
});

// Customer routes
Route::prefix('customer')->middleware([\App\Http\Middleware\CheckAuth::class, TwoFactorVerify::class, \App\Http\Middleware\CheckRole::class.':customer'])->name('customer.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-modern', [ModernCustomerDashboardController::class, 'index'])->name('dashboard.modern');
    Route::get('/my-orders', [CustomerDashboardController::class, 'orders'])->name('my-orders');
    Route::get('/saved-services', [CustomerDashboardController::class, 'savedServices'])->name('saved-services');
    
    // Service Marketplace
    Route::get('/marketplace', [ServiceMarketplaceController::class, 'index'])->name('marketplace');
    
    Route::get('/enterprises', function () {
        return redirect()->route('customer.marketplace');
    })->name('enterprises');
    Route::get('/enterprises/{id}/services', [CustomerController::class, 'enterpriseServices'])->whereUuid('id')->name('enterprise.services');
    Route::get('/services/{id}', [CustomerController::class, 'serviceDetails'])->whereUuid('id')->name('service.details');
    
    Route::post('/order', [CustomerController::class, 'placeOrder'])->name('order.place');
    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [CustomerController::class, 'orderDetails'])->whereUuid('id')->name('order.details');
    Route::post('/orders/{id}/confirm-completion', [CustomerController::class, 'confirmCompletion'])->whereUuid('id')->name('orders.confirm-completion');
    Route::post('/orders/{id}/reviews', [CustomerController::class, 'storeReview'])->whereUuid('id')->name('orders.reviews.store');
    Route::get('/orders/{id}/reviews-fragment', [CustomerController::class, 'orderReviewsFragment'])->whereUuid('id')->name('orders.reviews.fragment');
    Route::post('/orders/{id}/cancel', [CustomerController::class, 'cancelOrder'])->whereUuid('id')->name('orders.cancel');
    Route::post('/orders/{orderId}/extension-requests/{requestId}/respond', [CustomerController::class, 'respondOrderExtension'])->whereUuid('orderId')->whereUuid('requestId')->name('orders.extension.respond');
    
    // Design File Upload
    Route::post('/orders/{orderId}/upload-design', [CustomerController::class, 'uploadDesignFile'])->whereUuid('orderId')->name('orders.upload-design');
    Route::delete('/orders/{orderId}/design-files/{fileId}', [CustomerController::class, 'deleteDesignFile'])->whereUuid('orderId')->whereUuid('fileId')->name('orders.delete-design');
    
    // Notifications
    Route::get('/notifications', [CustomerController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read', [CustomerController::class, 'markNotificationRead'])->whereUuid('id')->name('notifications.read');
    
    Route::get('/design-assets', [CustomerController::class, 'designAssets'])->name('design-assets');
});

if (app()->environment('local')) {
    Route::get('/debug/enterprises', function() {
        $enterprises = \App\Models\Enterprise::with('services')->get();
        return response()->json([
            'count' => $enterprises->count(),
            'enterprises' => $enterprises->toArray()
        ]);
    });

    Route::get('/debug/services', function() {
        $services = \App\Models\Service::with(['enterprise', 'customizationOptions'])->get();
        return response()->json([
            'count' => $services->count(),
            'services' => $services->toArray()
        ]);
    });
}


// Chat routes (authenticated users)
Route::middleware([\App\Http\Middleware\CheckAuth::class])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/enterprise/{enterpriseId}', [ChatController::class, 'startEnterpriseChat'])
        ->whereUuid('enterpriseId')
        ->name('chat.enterprise');
});

Route::middleware([\App\Http\Middleware\CheckAuth::class, \App\Http\Middleware\CheckRole::class.':customer'])->group(function () {
    Route::post('/reports', [UserReportController::class, 'store'])->name('reports.store');
    Route::post('/system-feedback', [SystemFeedbackController::class, 'store'])->name('system-feedback.store');
});

