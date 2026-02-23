<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SendOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    /**
     * Send OTP to user's email
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'User not found.'], 404)
                : back()->withErrors(['email' => 'User not found.']);
        }

        // Rate limiting: max 3 attempts per minute per email
        $key = 'otp-send:' . $request->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $message = "Please wait {$seconds} seconds before requesting another code.";
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 429)
                : back()->withErrors(['email' => $message]);
        }

        RateLimiter::hit($key, 60);

        try {
            $otp = $user->generateOtp();
            $user->notify(new SendOTP($otp));

            Log::info('OTP sent successfully', ['email' => $user->email]);

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'OTP sent to your email! It will expire in 10 minutes.'])
                : back()->with('success', 'OTP sent to your email! It will expire in 10 minutes.');
        } catch (\Throwable $e) {
            Log::error('Failed to send OTP', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again later.'], 500)
                : back()->withErrors(['email' => 'Failed to send OTP. Please try again later.']);
        }
    }

    /**
     * Verify OTP code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'User not found.'], 404)
                : back()->withErrors(['otp' => 'User not found.']);
        }

        // Check if user has exceeded max attempts
        if ($user->hasExceededOtpAttempts(3)) {
            $user->clearOtp();
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Too many failed attempts. Please request a new OTP.'], 429)
                : back()->withErrors(['otp' => 'Too many failed attempts. Please request a new OTP.']);
        }

        // Check if OTP is valid
        if (!$user->hasValidOtp()) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new one.'], 400)
                : back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if (!$user->isValidOtp($request->otp)) {
            $user->incrementOtpAttempts();
            $remaining = 3 - $user->otp_attempts;
            $message = "Invalid OTP. {$remaining} attempts remaining.";
            
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 400)
                : back()->withErrors(['otp' => $message]);
        }

        // OTP is valid - clear it and proceed
        $user->clearOtp();

        // Store verification in session for protected actions
        session(['otp_verified_at' => now()->toIso8601String()]);
        session(['otp_verified_for' => $request->email]);

        // Handle enabling 2FA if requested
        if ($request->boolean('enable_two_factor')) {
            $user->forceFill(['two_factor_email_enabled' => true])->save();
            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'OTP verified and 2FA enabled successfully!'])
                : redirect()->intended()->with('success', '2FA enabled successfully!');
        }

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'OTP verified successfully!', 'redirect_url' => redirect()->intended()->getTargetUrl()])
            : redirect()->intended()->with('success', 'OTP verified successfully!');
    }

    /**
     * Disable OTP/2FA for user
     */
    public function disable(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Not authenticated.'], 401)
                : back()->withErrors(['error' => 'Not authenticated.']);
        }

        try {
            $user->forceFill(['two_factor_email_enabled' => false])->save();
            $user->clearOtp();

            Log::info('2FA disabled', ['user_id' => $user->user_id]);

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => '2FA disabled successfully.'])
                : back()->with('success', '2FA disabled successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to disable 2FA', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage(),
            ]);

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to disable 2FA.'], 500)
                : back()->withErrors(['error' => 'Failed to disable 2FA.']);
        }
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        return $this->send($request);
    }
}
