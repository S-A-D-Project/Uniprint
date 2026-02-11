@extends('layouts.public')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-10">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <i data-lucide="shield" class="h-8 w-8 text-primary"></i>
            <h1 class="text-3xl font-bold text-gray-900">Two-Factor Authentication</h1>
        </div>
        <p class="text-gray-600 text-lg">Secure your account with an authenticator app.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        @php
            $totpEnabled = (bool) ($totpEnabled ?? false);
            $emailEnabled = (bool) ($emailEnabled ?? false);
            $smsEnabled = (bool) ($smsEnabled ?? false);
        @endphp

        @if($totpEnabled || $emailEnabled || $smsEnabled)
            <div class="p-4 rounded-lg border border-success/20 bg-success/5 mb-6">
                <div class="font-semibold text-gray-900">Two-factor is active</div>
                <div class="text-sm text-gray-600">You will only be asked for a code after login when at least one method is enabled.</div>
            </div>
        @endif

        <div class="p-4 rounded-lg border border-gray-200 mb-6">
            <div class="font-semibold text-gray-900 mb-1">Authenticator App (TOTP)</div>
            <div class="text-sm text-gray-600 mb-4">Scan the QR code in your authenticator app and verify a code to enable.</div>

            @if($totpEnabled)
                <div class="text-sm text-gray-700 mb-4">Status: <span class="font-semibold">Enabled</span></div>
                <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-4" data-up-button-loader>
                    @csrf
                    <button type="submit" data-up-loading-text="Disabling..." class="inline-flex items-center gap-2 px-6 py-3 bg-destructive text-destructive-foreground font-medium rounded-lg hover:opacity-90 transition-colors">
                        <i data-lucide="shield-off" class="h-4 w-4"></i>
                        Disable Authenticator
                    </button>
                </form>
            @else
            <div class="p-4 rounded-lg border border-warning/20 bg-warning/5 mb-6">
                <div class="font-semibold text-gray-900">Enable Authenticator</div>
                <div class="text-sm text-gray-600">Scan the QR code using Google Authenticator / Microsoft Authenticator, then enter the generated code to confirm.</div>
            </div>

            @if(!empty($qrInline))
                <div class="flex items-center justify-center mb-6">
                    <div class="p-4 bg-white border border-gray-200 rounded-lg">{!! $qrInline !!}</div>
                </div>
            @else
                <div class="text-sm text-gray-600 mb-6">QR code unavailable. Please refresh the page.</div>
            @endif

            <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-4" data-up-button-loader>
                @csrf

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Authentication Code</label>
                    <input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required
                        class="w-full max-w-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors @error('code') border-destructive @enderror">
                    @error('code')
                        <p class="text-sm text-destructive mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" data-up-loading-text="Enabling..." class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    <i data-lucide="shield-check" class="h-4 w-4"></i>
                    Enable Authenticator
                </button>
            </form>
            @endif
        </div>

        <div class="p-4 rounded-lg border border-gray-200 mb-6">
            <div class="font-semibold text-gray-900 mb-1">Verification Methods</div>
            <div class="text-sm text-gray-600 mb-4">You can enable multiple methods at the same time.</div>

            <form method="POST" action="{{ route('two-factor.methods.update') }}" class="space-y-4" data-up-button-loader>
                @csrf

                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="font-medium text-gray-900">Email verification</div>
                        <div class="text-sm text-gray-600">Send codes to your primary account email.</div>
                    </div>
                    <input type="checkbox" name="two_factor_email_enabled" value="1" {{ $emailEnabled ? 'checked' : '' }} class="h-5 w-5">
                </div>
                @error('two_factor_email_enabled')
                    <p class="text-sm text-destructive">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="font-medium text-gray-900">SMS verification</div>
                        <div class="text-sm text-gray-600">Requires a phone number and SMS provider configuration.</div>
                    </div>
                    <input type="checkbox" name="two_factor_sms_enabled" value="1" {{ $smsEnabled ? 'checked' : '' }} class="h-5 w-5">
                </div>
                @error('two_factor_sms_enabled')
                    <p class="text-sm text-destructive">{{ $message }}</p>
                @enderror

                <button type="submit" data-up-loading-text="Saving..." class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Save Methods
                </button>
            </form>
        </div>

        <div class="mt-8">
            <a href="{{ route('profile.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg hover:bg-muted/40 transition-colors">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to Profile
            </a>
        </div>
    </div>
</div>
@endsection
