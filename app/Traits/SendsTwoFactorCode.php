<?php

namespace App\Traits;

use App\Notifications\TwoFactorCodeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait SendsTwoFactorCode
{
    public function generateTwoFactorCode(): void
    {
        $email = (string) ($this->email ?? '');
        if ($email === '') {
            throw new \RuntimeException('No email is associated with this account. Please add an email address first.');
        }

        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'two_factor_code' => $code,
            'two_factor_expires_at' => Carbon::now()->addMinutes(15),
        ])->save();

        try {
            $this->notify(new TwoFactorCodeNotification($code));
        } catch (\Throwable $e) {
            Log::error('Failed to send 2FA email notification', [
                'error' => $e->getMessage(),
                'user_id' => $this->getKey(),
                'mailer' => (string) config('mail.default'),
            ]);

            throw $e;
        }
    }

    public function resetTwoFactorCode(): void
    {
        $this->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();
    }

    public function hasValidTwoFactorCode(): bool
    {
        $code = (string) ($this->two_factor_code ?? '');
        if ($code === '') {
            return false;
        }

        $expiresAt = $this->two_factor_expires_at;
        if (!$expiresAt) {
            return false;
        }

        try {
            return Carbon::parse($expiresAt)->isFuture();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
