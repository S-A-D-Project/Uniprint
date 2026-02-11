<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class TwoFactorController extends Controller
{
    private const EMAIL_CODE_COOLDOWN_SECONDS = 30;

    public function securitySettings(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = Auth::user();

        $pendingEnable = false;
        $expiresAt = $user->two_factor_expires_at ?? null;
        $code = (string) ($user->two_factor_code ?? '');
        if (empty($user->two_factor_enabled) && $code !== '' && $expiresAt) {
            try {
                $pendingEnable = Carbon::parse($expiresAt)->isFuture();
            } catch (\Throwable $e) {
                $pendingEnable = false;
            }
        }

        return view('security.settings', [
            'twoFactorEnabled' => !empty($user->two_factor_enabled),
            'pendingEnable' => $pendingEnable,
            'roleType' => $roleType,
        ]);
    }

    public function startEnableEmail2fa(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = Auth::user();
        if (!empty($user->two_factor_enabled)) {
            return redirect()->route('security.settings');
        }

        $email = (string) ($user->email ?? '');
        if ($email === '') {
            return redirect()->route('security.settings')->with('error', 'No email is associated with your account. Please add/connect an email address first.');
        }

        $mailer = (string) config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return redirect()->route('security.settings')->with('error', 'Email delivery is not configured. Please configure Gmail SMTP first.');
        }

        try {
            if (method_exists($user, 'resetTwoFactorCode')) {
                $user->resetTwoFactorCode();
            }
            if (method_exists($user, 'generateTwoFactorCode')) {
                $user->generateTwoFactorCode();
            }
        } catch (\Throwable $e) {
            Log::error('2FA enable start failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'mailer' => (string) config('mail.default'),
            ]);
            return redirect()->route('security.settings')->with('error', 'Failed to send confirmation code. Please try again.');
        }

        return redirect()->route('security.settings')->with('success', 'A confirmation code has been sent to your email.');
    }

    public function resendEnableEmail2fa(Request $request)
    {
        return $this->startEnableEmail2fa($request);
    }

    public function confirmEnableEmail2fa(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = Auth::user();
        if (!empty($user->two_factor_enabled)) {
            return redirect()->route('security.settings');
        }

        $dbCode = (string) ($user->two_factor_code ?? '');
        $expiresAt = $user->two_factor_expires_at ?? null;
        if ($dbCode === '' || !$expiresAt) {
            return redirect()->route('security.settings')->with('error', 'Please request a confirmation code first.');
        }

        try {
            if (Carbon::parse($expiresAt)->isPast()) {
                if (method_exists($user, 'resetTwoFactorCode')) {
                    $user->resetTwoFactorCode();
                }
                return redirect()->route('security.settings')->with('error', 'The code has expired. Please resend a new one.');
            }
        } catch (\Throwable $e) {
            if (method_exists($user, 'resetTwoFactorCode')) {
                $user->resetTwoFactorCode();
            }
            return redirect()->route('security.settings')->with('error', 'The code has expired. Please resend a new one.');
        }

        if (!hash_equals($dbCode, (string) $request->input('code'))) {
            return redirect()->route('security.settings')->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        try {
            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_enabled' => true,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('2FA enable confirm failed', ['error' => $e->getMessage(), 'user_id' => $userId]);
            return redirect()->route('security.settings')->with('error', 'Failed to enable 2FA. Please try again.');
        }

        return redirect()->route('security.settings')->with('success', 'Email 2FA has been enabled.');
    }

    public function disableEmail2fa(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        try {
            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_enabled' => false,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('2FA disable failed', ['error' => $e->getMessage(), 'user_id' => $userId]);
            return redirect()->route('security.settings')->with('error', 'Failed to disable 2FA. Please try again.');
        }

        return redirect()->route('security.settings')->with('success', 'Email 2FA has been disabled.');
    }

    public function showVerify(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = \App\Models\User::find($userId);

        if (empty($user->two_factor_enabled)) {
            return $this->redirectAfterVerify($roleType);
        }

        $code = (string) ($user->two_factor_code ?? '');
        $expiresAt = $user->two_factor_expires_at ?? null;
        $hasActiveCode = $code !== '' && $expiresAt;
        if ($hasActiveCode) {
            try {
                if (Carbon::parse($expiresAt)->isPast()) {
                    if (method_exists($user, 'resetTwoFactorCode')) {
                        $user->resetTwoFactorCode();
                    }
                    $hasActiveCode = false;
                }
            } catch (\Throwable $e) {
                if (method_exists($user, 'resetTwoFactorCode')) {
                    $user->resetTwoFactorCode();
                }
                $hasActiveCode = false;
            }
        }

        if (!$hasActiveCode) {
            return $this->redirectAfterVerify($roleType);
        }

        return view('auth.verify-2fa');
    }

    public function submitVerify(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($user->two_factor_enabled)) {
            return $this->redirectAfterVerify($roleType);
        }

        $dbCode = (string) ($user->two_factor_code ?? '');
        $expiresAt = $user->two_factor_expires_at ?? null;

        if ($dbCode === '' || !$expiresAt) {
            return $this->redirectAfterVerify($roleType);
        }

        try {
            if (Carbon::parse($expiresAt)->isPast()) {
                if (method_exists($user, 'resetTwoFactorCode')) {
                    $user->resetTwoFactorCode();
                }
                return back()->withErrors(['code' => 'The code has expired. Please resend a new one.']);
            }
        } catch (\Throwable $e) {
            if (method_exists($user, 'resetTwoFactorCode')) {
                $user->resetTwoFactorCode();
            }
            return back()->withErrors(['code' => 'The code has expired. Please resend a new one.']);
        }

        if (!hash_equals($dbCode, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        if (method_exists($user, 'resetTwoFactorCode')) {
            $user->resetTwoFactorCode();
        }

        return $this->redirectAfterVerify($roleType);
    }

    public function resendVerifyCode(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();
        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $user = \App\Models\User::find($userId);
        if (empty($user->two_factor_enabled)) {
            return $this->redirectAfterVerify($roleType);
        }

        $mailer = (string) config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return back()->withErrors([
                'code' => 'Email delivery is not configured (MAIL_MAILER is set to log/array). Configure Gmail SMTP to receive verification codes.',
            ]);
        }

        if (method_exists($user, 'resetTwoFactorCode')) {
            $user->resetTwoFactorCode();
        }

        try {
            if (method_exists($user, 'generateTwoFactorCode')) {
                $user->generateTwoFactorCode();
            }
        } catch (\Throwable $e) {
            Log::error('2FA resend verify code failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'mailer' => (string) config('mail.default'),
            ]);
            return back()->withErrors(['code' => 'Failed to resend code. Please try again.']);
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    private function redirectAfterVerify(string $roleType)
    {
        $intended = (string) session('two_factor_intended', '');
        session()->forget('two_factor_intended');

        if ($intended !== '') {
            return redirect($intended);
        }

        if ($roleType === 'business_user') {
            return redirect()->route('business.dashboard');
        }
        if ($roleType === 'customer') {
            return redirect()->route('customer.dashboard');
        }

        return redirect()->route('home');
    }

    public function updateMethods(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'two_factor_email_enabled' => 'nullable|boolean',
            'two_factor_sms_enabled' => 'nullable|boolean',
        ]);

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return redirect()->route('login');
            }

            $emailEnabled = (bool) $request->boolean('two_factor_email_enabled');
            $smsEnabled = (bool) $request->boolean('two_factor_sms_enabled');

            if ($smsEnabled) {
                $phone = (string) ($user->phone ?? '');
                if ($phone === '') {
                    return back()->withErrors(['two_factor_sms_enabled' => 'Please add a phone number to enable SMS verification.']);
                }

                $twilioSid = (string) config('services.twilio.sid');
                $twilioToken = (string) config('services.twilio.token');
                $twilioFrom = (string) config('services.twilio.from');
                if ($twilioSid === '' || $twilioToken === '' || $twilioFrom === '') {
                    return back()->withErrors(['two_factor_sms_enabled' => 'SMS verification is unavailable. Please configure the SMS provider first.']);
                }
            }

            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_email_enabled' => $emailEnabled,
                'two_factor_sms_enabled' => $smsEnabled,
                'updated_at' => now(),
            ]);

            session()->forget([
                'two_factor_passed',
                'two_factor_email_code_hash',
                'two_factor_email_code_expires_at',
            ]);

            return redirect()->route('two-factor.setup')->with('success', 'Two-factor settings updated.');
        } catch (\Throwable $e) {
            Log::error('2FA methods update error', ['error' => $e->getMessage()]);
            return redirect()->route('two-factor.setup')->with('error', 'Failed to update two-factor settings.');
        }
    }

    public function sendEmailCode(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return redirect()->route('login');
            }

            if (empty($user->two_factor_email_enabled)) {
                return back()->withErrors(['email_code' => 'Email verification is not enabled for your account.']);
            }

            $hash = (string) session('two_factor_email_code_hash', '');
            $expiresAt = (int) session('two_factor_email_code_expires_at', 0);
            $hasValidCode = $hash !== '' && $expiresAt > time();

            if ($hasValidCode) {
                return back()->with('success', 'A verification code was already sent to your email.');
            }

            $lastSentAt = (int) session('two_factor_email_code_last_sent_at', 0);
            $cooldownRemaining = self::EMAIL_CODE_COOLDOWN_SECONDS - (time() - $lastSentAt);
            if ($lastSentAt > 0 && $cooldownRemaining > 0) {
                return back()->withErrors(['email_code' => 'Please wait a moment before requesting another code.']);
            }

            $email = (string) ($user->email ?? '');
            if ($email === '') {
                return back()->withErrors(['email_code' => 'No email address is associated with your account.']);
            }

            $mailer = (string) config('mail.default');
            if (in_array($mailer, ['log', 'array'], true)) {
                return back()->withErrors([
                    'email_code' => 'Email delivery is not configured (MAIL_MAILER is set to log/array). Configure SMTP to receive verification codes.',
                ]);
            }

            $code = (string) random_int(100000, 999999);

            session([
                'two_factor_email_code_hash' => Hash::make($code),
                'two_factor_email_code_expires_at' => now()->addMinutes(10)->timestamp,
                'two_factor_email_code_last_sent_at' => time(),
            ]);

            Mail::raw("Your UniPrint verification code is: {$code}\n\nThis code will expire in 10 minutes.", function ($message) use ($email) {
                $message->to($email)->subject('UniPrint verification code');
            });

            return back()->with('success', 'A verification code has been sent to your email.');
        } catch (\Throwable $e) {
            Log::error('2FA email code send error', [
                'error' => $e->getMessage(),
                'mailer' => (string) config('mail.default'),
                'user_id' => session('user_id'),
            ]);
            return back()->withErrors(['email_code' => 'Failed to send email code. Please try again.']);
        }
    }

    public function verifyEmailCode(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'email_code' => 'required|string|max:10',
        ]);

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return redirect()->route('login');
            }
            if (empty($user->two_factor_email_enabled)) {
                return back()->withErrors(['email_code' => 'Email verification is not enabled for your account.']);
            }

            $hash = (string) session('two_factor_email_code_hash', '');
            $expiresAt = (int) session('two_factor_email_code_expires_at', 0);

            if ($hash === '' || $expiresAt <= 0) {
                return back()->withErrors(['email_code' => 'Please request a new code.']);
            }
            if (time() > $expiresAt) {
                session()->forget(['two_factor_email_code_hash', 'two_factor_email_code_expires_at']);
                return back()->withErrors(['email_code' => 'The code has expired. Please request a new one.']);
            }

            $code = (string) $request->input('email_code');
            if (!Hash::check($code, $hash)) {
                return back()->withErrors(['email_code' => 'Invalid code. Please try again.']);
            }

            session()->forget(['two_factor_email_code_hash', 'two_factor_email_code_expires_at']);
            session(['two_factor_passed' => true]);

            $intended = (string) session('two_factor_intended', '');
            session()->forget('two_factor_intended');

            if ($intended !== '') {
                return redirect($intended);
            }

            return redirect()->route('home');
        } catch (\Throwable $e) {
            Log::error('2FA email verify error', ['error' => $e->getMessage()]);
            return back()->withErrors(['email_code' => 'Failed to verify code. Please try again.']);
        }
    }

    public function setup()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) {
            return redirect()->route('login');
        }

        $totpEnabled = !empty($user->two_factor_totp_enabled) && !empty($user->two_factor_enabled_at) && !empty($user->two_factor_secret);
        $emailEnabled = !empty($user->two_factor_email_enabled);
        $smsEnabled = !empty($user->two_factor_sms_enabled);

        $secret = (string) ($user->two_factor_secret ?? '');
        if (!$totpEnabled && $secret === '') {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_secret' => $secret,
                'updated_at' => now(),
            ]);
        }

        $qrInline = '';
        if (!$totpEnabled && $secret !== '') {
            try {
                $g2fa = new Google2FAQRCode();
                $qrInline = (string) $g2fa->getQRCodeInline('UniPrint', (string) ($user->email ?? $user->name ?? 'user'), $secret);
            } catch (\Throwable $e) {
                Log::warning('2FA QR generation failed', ['error' => $e->getMessage()]);
                $qrInline = '';
            }
        }

        return view('profile.two-factor', [
            'totpEnabled' => $totpEnabled,
            'emailEnabled' => $emailEnabled,
            'smsEnabled' => $smsEnabled,
            'qrInline' => $qrInline,
        ]);
    }

    public function enable(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|max:10',
        ]);

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return redirect()->route('login');
            }

            $secret = (string) ($user->two_factor_secret ?? '');
            if ($secret === '') {
                return redirect()->route('two-factor.setup')->with('error', 'Two-factor secret missing. Please refresh and try again.');
            }

            $google2fa = new Google2FA();
            $ok = $google2fa->verifyKey($secret, (string) $request->input('code'));
            if (!$ok) {
                return back()->withErrors([
                    'code' => 'Invalid authentication code. Please try again.',
                ]);
            }

            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_enabled_at' => now(),
                'two_factor_totp_enabled' => true,
                'updated_at' => now(),
            ]);

            session()->forget('two_factor_passed');

            return redirect()->route('profile.index')->with('success', 'Two-factor authentication enabled.');
        } catch (\Throwable $e) {
            Log::error('2FA enable error', ['error' => $e->getMessage()]);
            return redirect()->route('two-factor.setup')->with('error', 'Failed to enable two-factor authentication. Please try again.');
        }
    }

    public function disable(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        try {
            DB::table('users')->where('user_id', $userId)->update([
                'two_factor_totp_enabled' => false,
                'updated_at' => now(),
            ]);
            session()->forget('two_factor_passed');

            return redirect()->route('two-factor.setup')->with('success', 'Authenticator verification disabled.');
        } catch (\Throwable $e) {
            Log::error('2FA disable error', ['error' => $e->getMessage()]);
            return redirect()->route('two-factor.setup')->with('error', 'Failed to update two-factor authentication.');
        }
    }

    public function challenge()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!empty($user->two_factor_enabled)) {
            return redirect()->route('two-factor.verify');
        }

        $totpEnabled = !empty($user->two_factor_totp_enabled) && !empty($user->two_factor_enabled_at) && !empty($user->two_factor_secret);
        $emailEnabled = !empty($user->two_factor_email_enabled);
        $smsEnabled = !empty($user->two_factor_sms_enabled);

        if ($emailEnabled && empty($user->email)) {
            $emailEnabled = false;
            session()->flash('error', 'No email is associated with your account. Please add/connect an email address first.');
        }

        if (!$totpEnabled && !$emailEnabled && !$smsEnabled) {
            session(['two_factor_passed' => true]);
            $intended = (string) session('two_factor_intended', '');
            session()->forget('two_factor_intended');
            return $intended !== '' ? redirect($intended) : redirect()->route('home');
        }

        // Auto-send email code if email 2FA is the only method or if it's enabled and no valid code exists
        if ($emailEnabled) {
            $hash = (string) session('two_factor_email_code_hash', '');
            $expiresAt = (int) session('two_factor_email_code_expires_at', 0);
            $hasValidCode = $hash !== '' && $expiresAt > time();

            if (!$hasValidCode) {
                $lastSentAt = (int) session('two_factor_email_code_last_sent_at', 0);
                $cooldownPassed = $lastSentAt <= 0 || (time() - $lastSentAt) >= self::EMAIL_CODE_COOLDOWN_SECONDS;

                if ($cooldownPassed) {
                    try {
                        $email = (string) ($user->email ?? '');
                        if ($email !== '') {
                            $code = (string) random_int(100000, 999999);
                            session([
                                'two_factor_email_code_hash' => Hash::make($code),
                                'two_factor_email_code_expires_at' => now()->addMinutes(10)->timestamp,
                                'two_factor_email_code_last_sent_at' => time(),
                            ]);

                            Mail::raw("Your UniPrint verification code is: {$code}\n\nThis code will expire in 10 minutes.", function ($message) use ($email) {
                                $message->to($email)->subject('UniPrint verification code');
                            });

                            session()->flash('success', 'A verification code has been sent to your email.');
                        }
                    } catch (\Throwable $e) {
                        Log::error('2FA email code auto-send error', ['error' => $e->getMessage()]);
                    }
                }
            }
        }

        return view('auth.two-factor-challenge', [
            'totpEnabled' => $totpEnabled,
            'emailEnabled' => $emailEnabled,
            'smsEnabled' => $smsEnabled,
        ]);
    }

    public function verify(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|max:10',
        ]);

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            $secret = (string) ($user->two_factor_secret ?? '');

            if (empty($user->two_factor_totp_enabled) || $secret === '' || empty($user->two_factor_enabled_at)) {
                return redirect()->route('two-factor.setup')->with('error', 'Authenticator verification is not enabled for your account.');
            }

            $google2fa = new Google2FA();
            $ok = $google2fa->verifyKey($secret, (string) $request->input('code'));

            if (!$ok) {
                return back()->withErrors([
                    'code' => 'Invalid authentication code. Please try again.',
                ]);
            }

            session([
                'two_factor_passed' => true,
            ]);

            $intended = (string) session('two_factor_intended', '');
            session()->forget('two_factor_intended');

            if ($intended !== '') {
                return redirect($intended);
            }

            return redirect()->route('home');
        } catch (\Throwable $e) {
            Log::error('2FA verify error', ['error' => $e->getMessage()]);
            return back()->withErrors([
                'code' => 'Failed to verify authentication code. Please try again.',
            ]);
        }
    }
}
