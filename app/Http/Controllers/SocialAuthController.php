<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    private const GOOGLE_REDIRECT_URL_SESSION_KEY = 'oauth_google_redirect_url';

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        $this->storeRoleIntentFromRequest();

        $redirectUrl = $this->resolveGoogleRedirectUrl();
        session([self::GOOGLE_REDIRECT_URL_SESSION_KEY => $redirectUrl]);

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $redirectUrl = session(self::GOOGLE_REDIRECT_URL_SESSION_KEY) ?: $this->resolveGoogleRedirectUrl();
            $googleUser = Socialite::driver('google')->stateless()->redirectUrl($redirectUrl)->user();
            session()->forget(self::GOOGLE_REDIRECT_URL_SESSION_KEY);
            return $this->handleSocialLogin($googleUser, 'google');
        } catch (Exception $e) {
            \Log::error('Google OAuth error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors(['social' => 'Failed to authenticate with Google. Please try again.']);
        }
    }

    private function resolveGoogleRedirectUrl(): string
    {
        $local = config('services.google.redirect_local') ?: env('GOOGLE_REDIRECT_URI_LOCAL');
        $herd = config('services.google.redirect_herd') ?: env('GOOGLE_REDIRECT_URI_HERD');

        $fallback = config('services.google.redirect');
        if (is_string($fallback) && str_starts_with($fallback, '/')) {
            $fallback = url($fallback);
        }

        $host = request()->getHost();
        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return $local ?: ($fallback ?: route('auth.google.callback'));
        }

        $fwdHostFallback = $this->resolveFwdHostRedirectUrl();
        return $herd ?: ($fwdHostFallback ?: ($fallback ?: route('auth.google.callback')));
    }

    private function resolveFwdHostRedirectUrl(): ?string
    {
        $host = request()->getHost();
        if (!is_string($host) || $host === '') {
            return null;
        }

        if (!str_ends_with($host, '.test')) {
            return null;
        }

        return 'https://fwd.host/https://' . $host . '/auth/callback';
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        $this->storeRoleIntentFromRequest();
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            return $this->handleSocialLogin($facebookUser, 'facebook');
        } catch (Exception $e) {
            \Log::error('Facebook OAuth error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors(['social' => 'Failed to authenticate with Facebook. Please try again.']);
        }
    }

    /**
     * Handle social login logic
     */
    private function handleSocialLogin($socialUser, $provider)
    {
        try {
            // Get email safely (may be null for some providers)
            $email = $socialUser->getEmail();
            $name = $socialUser->getName();
            $providerId = $socialUser->getId();
            
            // Validate required data
            if (!$providerId) {
                \Log::warning("Social login error: No provider ID from $provider");
                return redirect()->route('login')->withErrors(['social' => 'Failed to get user information from ' . ucfirst($provider) . '. Please try again.']);
            }
            
            if (!$name) {
                $name = 'User ' . substr($providerId, 0, 8);
            }
            
            // Check if social login already exists
            $socialLogin = DB::table('social_logins')
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($socialLogin && $socialLogin->user_id) {
                // Existing social login - log them in
                $user = DB::table('users')
                    ->where('user_id', $socialLogin->user_id)
                    ->first();

                if ($user) {
                    $this->createSession($user);
                    return $this->redirectAfterLogin($user->user_id);
                }
            }

            // Check if user exists by email (only if email is available)
            $user = null;
            if ($email) {
                $user = DB::table('users')
                    ->where('email', $email)
                    ->first();
            }

            if ($user) {
                // Link social login to existing user
                $existingSocialLogin = DB::table('social_logins')
                    ->where('user_id', $user->user_id)
                    ->where('provider', $provider)
                    ->first();

                if (!$existingSocialLogin) {
                    DB::table('social_logins')->insert([
                        'social_login_id' => Str::uuid(),
                        'user_id' => $user->user_id,
                        'provider' => $provider,
                        'provider_id' => $providerId,
                        'email' => $email,
                        'name' => $name,
                        'avatar_url' => $socialUser->getAvatar(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->createSession($user);
                return $this->redirectAfterLogin($user->user_id);
            }

            // Create new user
            $userId = Str::uuid();
            $username = $this->generateUniqueUsername($name);
            
            // Generate email if not provided
            if (!$email) {
                $email = $provider . '_' . $providerId . '@uniprint.local';
            }

            // Create user record
            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => $name,
                'email' => $email,
                'position' => 'Customer',
                'department' => 'External',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create social login record
            DB::table('social_logins')->insert([
                'social_login_id' => Str::uuid(),
                'user_id' => $userId,
                'provider' => $provider,
                'provider_id' => $providerId,
                'email' => $email,
                'name' => $name,
                'avatar_url' => $socialUser->getAvatar(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $desiredRoleType = session('oauth_role_type', 'customer');
            if (!in_array($desiredRoleType, ['customer', 'business_user'], true)) {
                $desiredRoleType = 'customer';
            }

            $roleTypeId = $this->ensureRoleType($desiredRoleType);
            if ($roleTypeId) {
                DB::table('roles')->insert([
                    'role_id' => Str::uuid(),
                    'user_id' => $userId,
                    'role_type_id' => $roleTypeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            session()->forget('oauth_role_type');

            $this->createSession(DB::table('users')->where('user_id', $userId)->first());
            return $this->redirectAfterLogin($userId);

        } catch (Exception $e) {
            \Log::error('Social login error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->route('login')->withErrors(['social' => 'An error occurred during social login. Please try again.']);
        }
    }

    private function storeRoleIntentFromRequest(): void
    {
        $raw = request()->query('role_type');
        if (!$raw) {
            return;
        }

        if ($raw === 'business') {
            session(['oauth_role_type' => 'business_user']);
            return;
        }

        if (in_array($raw, ['customer', 'business_user'], true)) {
            session(['oauth_role_type' => $raw]);
        }
    }

    private function redirectAfterLogin(string $userId)
    {
        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->select('role_types.user_role_type')
            ->first();

        if ($role && $role->user_role_type === 'business_user') {
            $hasEnterprise = DB::table('enterprises')->where('owner_user_id', $userId)->exists();
            if (! $hasEnterprise) {
                $hasEnterprise = DB::table('staff')->where('user_id', $userId)->exists();
            }
            if (!$hasEnterprise) {
                return redirect()->route('business.onboarding');
            }
            return redirect()->route('business.dashboard');
        }

        if ($role && $role->user_role_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }

    private function ensureRoleType(string $userRoleType): ?string
    {
        $roleType = DB::table('role_types')
            ->where('user_role_type', $userRoleType)
            ->first();

        if ($roleType && $roleType->role_type_id) {
            return $roleType->role_type_id;
        }

        $roleTypeId = (string) Str::uuid();
        DB::table('role_types')->insert([
            'role_type_id' => $roleTypeId,
            'user_role_type' => $userRoleType,
        ]);

        return $roleTypeId;
    }

    /**
     * Create user session
     */
    private function createSession($user)
    {
        session([
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now(),
        ]);

        request()->session()->regenerate();
        request()->session()->regenerateToken();
    }

    /**
     * Generate unique username from name
     */
    private function generateUniqueUsername($name)
    {
        $baseUsername = strtolower(str_replace(' ', '', $name));
        $baseUsername = preg_replace('/[^a-z0-9_\-\.]/', '', $baseUsername);
        
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;

        while (DB::table('login')->where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
