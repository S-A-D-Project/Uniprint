<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            return $this->handleSocialLogin($googleUser, 'google');
        } catch (Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['social' => 'Failed to authenticate with Google. Please try again.']);
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            return $this->handleSocialLogin($facebookUser, 'facebook');
        } catch (Exception $e) {
            \Log::error('Facebook OAuth error: ' . $e->getMessage());
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
                    return redirect()->route('customer.dashboard');
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
                return redirect()->route('customer.dashboard');
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

            // Assign customer role
            $customerRoleType = DB::table('role_types')
                ->where('user_role_type', 'customer')
                ->first();

            if ($customerRoleType) {
                DB::table('roles')->insert([
                    'role_id' => Str::uuid(),
                    'user_id' => $userId,
                    'role_type_id' => $customerRoleType->role_type_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->createSession(DB::table('users')->where('user_id', $userId)->first());
            return redirect()->route('customer.dashboard');

        } catch (Exception $e) {
            \Log::error('Social login error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->route('login')->withErrors(['social' => 'An error occurred during social login. Please try again.']);
        }
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
