<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class ProfileController extends Controller
{
    private function paypalWebBaseUrl(): string
    {
        $mode = (string) config('services.paypal.mode', 'sandbox');
        return $mode === 'live' ? 'https://www.paypal.com' : 'https://www.sandbox.paypal.com';
    }

    private function paypalApiBaseUrl(): string
    {
        $mode = (string) config('services.paypal.mode', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Show user profile
     */
    public function index()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to view your profile');
        }

        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found');
        }

        $roleInfo = null;
        $enterprise = null;
        $orderStats = null;
        $recentOrders = collect();
        $linkedProviders = collect();
        $paypalConnected = false;
        $paypalAccountEmail = null;

        try {
            // Get user's role information
            $roleInfo = DB::table('roles')
                ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->where('roles.user_id', $userId)
                ->first();

            // Get user's enterprise information (if business user)
            if ($roleInfo && $roleInfo->user_role_type === 'business_user') {
                if (\Illuminate\Support\Facades\schema_has_column('enterprises', 'owner_user_id')) {
                    $enterprise = DB::table('enterprises')
                        ->where('owner_user_id', $userId)
                        ->first();
                }

                if (! $enterprise && \Illuminate\Support\Facades\schema_has_table('staff')) {
                    $enterprise = DB::table('staff')
                        ->join('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
                        ->where('staff.user_id', $userId)
                        ->first();
                }
            }
        } catch (\Exception $e) {
            Log::error('Profile View Error (role/enterprise)', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        try {
            // Get order statistics
            $totalOrders = DB::table('customer_orders')
                ->where('customer_id', $userId)
                ->count();

            $recentOrdersCount = DB::table('customer_orders')
                ->where('customer_id', $userId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            $orderStats = (object) [
                'total_orders' => $totalOrders,
                'recent_orders' => $recentOrdersCount,
            ];
        } catch (\Exception $e) {
            Log::error('Profile View Error (orderStats)', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        try {
            // Get recent orders (avoid GROUP BY issues on strict SQL modes)
            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $recentOrders = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*',
                    'enterprises.name as enterprise_name',
                    'statuses.status_name'
                )
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Profile View Error (recentOrders)', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $linkedProviders = DB::table('social_logins')
                ->where('user_id', $userId)
                ->pluck('provider')
                ->map(fn ($p) => strtolower((string) $p))
                ->unique()
                ->values();
        } catch (\Exception $e) {
            Log::error('Profile View Error (linkedProviders)', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        try {
            if (schema_has_table('user_payment_accounts')) {
                $paypal = DB::table('user_payment_accounts')
                    ->where('user_id', $userId)
                    ->where('provider', 'paypal')
                    ->first();

                $paypalConnected = !empty($paypal);
                $paypalAccountEmail = $paypal?->email ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Profile View Error (paypal)', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        return view('profile.index', compact('user', 'roleInfo', 'enterprise', 'orderStats', 'recentOrders', 'linkedProviders', 'paypalConnected', 'paypalAccountEmail'));
    }

    public function redirectToPayPalConnect(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        $clientId = (string) config('services.paypal.client_id');
        $clientSecret = (string) config('services.paypal.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('profile.index')->with('error', 'PayPal is not configured.');
        }

        $state = (string) Str::uuid();
        session(['paypal_oauth_state' => $state]);

        $redirectUri = route('profile.paypal.callback');
        $scopes = [
            'openid',
            'email',
            'https://uri.paypal.com/services/paypalattributes',
        ];

        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return redirect()->away($this->paypalWebBaseUrl() . '/signin/authorize?' . $query);
    }

    public function handlePayPalConnectCallback(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        $clientId = (string) config('services.paypal.client_id');
        $clientSecret = (string) config('services.paypal.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('profile.index')->with('error', 'PayPal is not configured.');
        }

        $code = (string) $request->query('code', '');
        $state = (string) $request->query('state', '');
        $expectedState = (string) session('paypal_oauth_state', '');
        session()->forget('paypal_oauth_state');

        if ($code === '') {
            $desc = (string) $request->query('error_description', $request->query('error', ''));
            return redirect()->route('profile.index')->with('error', $desc !== '' ? $desc : 'PayPal authorization failed.');
        }
        if ($expectedState === '' || $state !== $expectedState) {
            return redirect()->route('profile.index')->with('error', 'Invalid PayPal state. Please try again.');
        }

        try {
            $redirectUri = route('profile.paypal.callback');
            $tokenRes = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post($this->paypalApiBaseUrl() . '/v1/oauth2/token', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ]);

            if (!$tokenRes->ok()) {
                Log::error('PayPal connect token exchange failed', ['body' => $tokenRes->body()]);
                return redirect()->route('profile.index')->with('error', 'Failed to connect PayPal.');
            }

            $accessToken = (string) ($tokenRes->json('access_token') ?? '');
            $refreshToken = (string) ($tokenRes->json('refresh_token') ?? '');
            $expiresIn = (int) ($tokenRes->json('expires_in') ?? 0);
            $scope = (string) ($tokenRes->json('scope') ?? '');

            if ($accessToken === '') {
                return redirect()->route('profile.index')->with('error', 'PayPal access token missing.');
            }

            $userInfoRes = Http::withToken($accessToken)
                ->get($this->paypalApiBaseUrl() . '/v1/identity/oauth2/userinfo', [
                    'schema' => 'paypalv1.1',
                ]);

            if (!$userInfoRes->ok()) {
                Log::error('PayPal connect userinfo failed', ['body' => $userInfoRes->body()]);
                return redirect()->route('profile.index')->with('error', 'Failed to retrieve PayPal account information.');
            }

            $providerAccountId = (string) ($userInfoRes->json('user_id') ?? '');
            $email = (string) ($userInfoRes->json('emails.0.value') ?? $userInfoRes->json('email') ?? '');

            $expiresAt = null;
            if ($expiresIn > 0) {
                $expiresAt = now()->addSeconds($expiresIn);
            }

            $payload = [
                'provider_account_id' => $providerAccountId !== '' ? $providerAccountId : null,
                'email' => $email !== '' ? $email : null,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken !== '' ? $refreshToken : null,
                'token_expires_at' => $expiresAt,
                'scope' => $scope !== '' ? $scope : null,
                'metadata' => json_encode($userInfoRes->json(), JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ];

            if (!schema_has_table('user_payment_accounts')) {
                return redirect()->route('profile.index')->with('error', 'Payment accounts table missing. Please run migrations.');
            }

            $existing = DB::table('user_payment_accounts')
                ->where('user_id', $userId)
                ->where('provider', 'paypal')
                ->first();

            if ($existing) {
                DB::table('user_payment_accounts')
                    ->where('user_payment_account_id', $existing->user_payment_account_id)
                    ->update($payload);
            } else {
                DB::table('user_payment_accounts')->insert(array_merge($payload, [
                    'user_payment_account_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'provider' => 'paypal',
                    'created_at' => now(),
                ]));
            }

            return redirect()->route('profile.index')->with('success', 'PayPal account linked successfully.');
        } catch (\Throwable $e) {
            Log::error('PayPal connect callback error', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return redirect()->route('profile.index')->with('error', 'Failed to connect PayPal. Please try again.');
        }
    }

    public function disconnectPayPal(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        try {
            if (schema_has_table('user_payment_accounts')) {
                DB::table('user_payment_accounts')
                    ->where('user_id', $userId)
                    ->where('provider', 'paypal')
                    ->delete();
            }
            return redirect()->route('profile.index')->with('success', 'PayPal account disconnected.');
        } catch (\Throwable $e) {
            Log::error('PayPal disconnect error', [
                'user_id' => $userId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return redirect()->route('profile.index')->with('error', 'Failed to disconnect PayPal.');
        }
    }

    public function redirectToFacebookConnect()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        return Socialite::driver('facebook')
            ->scopes(['email'])
            ->redirectUrl(route('profile.connect-facebook.callback'))
            ->stateless()
            ->redirect();
    }

    public function handleFacebookConnectCallback()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        try {
            $fbUser = Socialite::driver('facebook')
                ->redirectUrl(route('profile.connect-facebook.callback'))
                ->stateless()
                ->user();
            $providerId = $fbUser->getId();

            if (!$providerId) {
                return redirect()->route('profile.index')->with('error', 'Failed to get Facebook user information.');
            }

            $existing = DB::table('social_logins')
                ->where('provider', 'facebook')
                ->where('provider_id', $providerId)
                ->first();

            if ($existing && $existing->user_id && $existing->user_id !== $userId) {
                return redirect()->route('profile.index')->with('error', 'This Facebook account is already linked to another UniPrint account.');
            }

            if ($existing) {
                DB::table('social_logins')
                    ->where('social_login_id', $existing->social_login_id)
                    ->update([
                        'user_id' => $userId,
                        'email' => $fbUser->getEmail(),
                        'name' => $fbUser->getName(),
                        'avatar_url' => $fbUser->getAvatar(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('social_logins')->insert([
                    'social_login_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'provider' => 'facebook',
                    'provider_id' => $providerId,
                    'email' => $fbUser->getEmail(),
                    'name' => $fbUser->getName(),
                    'avatar_url' => $fbUser->getAvatar(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $fbEmail = $fbUser->getEmail();
            if ($fbEmail) {
                $user = DB::table('users')->where('user_id', $userId)->first();
                if ($user) {
                    $currentEmail = (string) ($user->email ?? '');
                    $hasPlaceholderEmail = $currentEmail === '' || str_ends_with($currentEmail, '@uniprint.local');

                    if ($hasPlaceholderEmail) {
                        $emailTaken = DB::table('users')
                            ->where('email', $fbEmail)
                            ->where('user_id', '!=', $userId)
                            ->exists();

                        if (!$emailTaken) {
                            DB::table('users')
                                ->where('user_id', $userId)
                                ->update([
                                    'email' => $fbEmail,
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                }
            }

            return redirect()->route('profile.index')->with('success', 'Facebook account connected successfully.');
        } catch (\Exception $e) {
            Log::error('Facebook connect error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('profile.index')->with('error', 'Failed to connect Facebook. Please try again.');
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            // Check if email is already taken by another user
            $existingUser = DB::table('users')
                ->where('email', $request->email)
                ->where('user_id', '!=', $userId)
                ->first();
            
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already taken by another user'
                ], 422);
            }

            // Update user profile
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'bio' => $request->bio,
                'updated_at' => now()
            ];

            DB::table('users')
                ->where('user_id', $userId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Password Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 500);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = 'profile-' . $userId . '-' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store file
                $disk = config('filesystems.default', 'public');
                $path = $file->storeAs('profile-pictures', $filename, $disk);
                
                // Update user profile picture
                DB::table('users')
                    ->where('user_id', $userId)
                    ->update([
                        'profile_picture' => $path,
                        'updated_at' => now()
                    ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile picture updated successfully!',
                    'profile_picture_url' => Storage::url($path)
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Profile Picture Upload Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture'
            ], 500);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect'
                ], 422);
            }

            // Soft delete user (mark as inactive)
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'is_active' => false,
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            // Logout user
            session()->forget('user_id');
            session()->forget('user_role');

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Account Deletion Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account'
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function getNotifications()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $notifications = DB::table('notifications')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $unreadCount = DB::table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Notifications Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $updated = DB::table('notifications')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Mark Notification Read Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }
}
