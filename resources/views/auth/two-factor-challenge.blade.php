<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Challenge - UniPrint</title>

    <script>
        (function () {
            const orig = console.warn;
            console.warn = function (...args) {
                if (args && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) {
                    return;
                }
                return orig.apply(console, args);
            };
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: 'hsl(0 0% 100%)',
                        foreground: 'hsl(240 10% 3.9%)',
                        card: 'hsl(0 0% 100%)',
                        'card-foreground': 'hsl(240 10% 3.9%)',
                        primary: 'hsl(263 70% 50%)',
                        'primary-foreground': 'hsl(0 0% 100%)',
                        secondary: 'hsl(240 4.8% 95.9%)',
                        'secondary-foreground': 'hsl(240 5.9% 10%)',
                        muted: 'hsl(240 4.8% 95.9%)',
                        'muted-foreground': 'hsl(240 3.8% 46.1%)',
                        accent: 'hsl(16 90% 62%)',
                        'accent-foreground': 'hsl(0 0% 100%)',
                        destructive: 'hsl(0 84.2% 60.2%)',
                        'destructive-foreground': 'hsl(0 0% 100%)',
                        border: 'hsl(240 5.9% 90%)',
                        input: 'hsl(240 5.9% 90%)',
                        ring: 'hsl(263 70% 50%)',
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-accent/5 flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-bold">Two-Factor Authentication</h1>
            <p class="text-muted-foreground">Verify your identity to continue</p>
        </div>

        <div class="bg-card border border-border rounded-xl shadow-card-hover p-6">
            <div class="space-y-6">
                @if (session('success'))
                    <div class="px-4 py-3 rounded-md border border-border bg-secondary text-secondary-foreground text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="px-4 py-3 rounded-md border border-destructive bg-destructive/10 text-destructive text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @php
                    $totpEnabled = (bool) ($totpEnabled ?? false);
                    $emailEnabled = (bool) ($emailEnabled ?? false);
                    $smsEnabled = (bool) ($smsEnabled ?? false);
                    $emailExpiresAt = (int) session('two_factor_email_code_expires_at', 0);
                    $hasActiveEmailCode = (string) session('two_factor_email_code_hash', '') !== '' && $emailExpiresAt > time();
                @endphp

                @if($totpEnabled)
                    <form method="POST" action="{{ route('two-factor.totp.verify') }}" class="space-y-4" data-up-button-loader>
                        @csrf

                        <div class="space-y-2">
                            <label for="code" class="text-sm font-medium">Authentication Code</label>
                            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" required autofocus class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('code') border-destructive @enderror">
                            @error('code')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" data-up-loading-text="Verifying..." class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Verify
                        </button>
                    </form>
                @endif

                @if($totpEnabled && $emailEnabled)
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-border"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-card text-muted-foreground">Or verify via email</span>
                        </div>
                    </div>
                @endif

                @if($emailEnabled)
                    <form method="POST" action="{{ route('two-factor.email.send') }}" class="space-y-2" data-up-button-loader>
                        @csrf
                        <button type="submit" data-up-loading-text="Sending..." class="w-full px-4 py-2 bg-secondary text-secondary-foreground font-medium rounded-md hover:opacity-90 transition-smooth">
                            {{ $hasActiveEmailCode ? 'Resend code to my email' : 'Send code to my email' }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.email.verify') }}" class="space-y-2" data-up-button-loader>
                        @csrf
                        <label for="email_code" class="text-sm font-medium">Email Code</label>
                        <input id="email_code" name="email_code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('email_code') border-destructive @enderror">
                        @error('email_code')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                        <button type="submit" data-up-loading-text="Verifying..." class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Verify email code
                        </button>
                    </form>
                @endif

                <div class="text-center text-sm text-muted-foreground">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-primary hover:underline">Logout</a>
                </div>
            </div>

            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>

        <script>
            lucide.createIcons();
        </script>

        <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    </div>
</body>
</html>
