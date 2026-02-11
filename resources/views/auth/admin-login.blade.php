<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - UniPrint</title>
    
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

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom TailwindCSS Configuration -->
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
                        warning: 'hsl(38 92% 50%)',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <style>
        .gradient-primary {
            background: linear-gradient(135deg, hsl(263 70% 50%), hsl(263 85% 65%));
        }
        .shadow-card-hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        .shadow-glow {
            box-shadow: 0 0 40px hsl(263 70% 50% / 0.3);
        }
        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .turnstile-wrap {
            display: flex;
            justify-content: center;
            width: 100%;
            overflow: hidden;
        }

        @media (max-width: 380px) {
            .turnstile-wrap {
                transform: scale(0.92);
                transform-origin: center;
            }
        }
    </style>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    @if(!empty(config('services.turnstile.site_key')))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endif
</head>
<body class="min-h-screen bg-gradient-to-br from-primary/10 via-background to-accent/10 flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <div class="flex items-center justify-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-primary hover:opacity-80 transition-smooth">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                <span class="text-sm">Back to Home</span>
            </a>
        </div>

        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 mb-4">
                <i data-lucide="shield" class="h-8 w-8 text-primary"></i>
            </div>
            <h1 class="text-3xl font-bold">Admin Login</h1>
            <p class="text-muted-foreground">Secure access for system administrators</p>
        </div>

        <div class="bg-card border border-primary/20 rounded-xl shadow-card-hover">
            <div class="p-6 border-b border-border">
                <h3 class="text-lg font-bold">Administrator Access</h3>
                <p class="text-sm text-muted-foreground">
                    This area is restricted to authorized personnel only
                </p>
            </div>
            
            <form method="POST" action="{{ route('login') }}" data-up-button-loader>
                @csrf
                <input type="hidden" name="role_type" value="admin">
                
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label for="admin-username" class="text-sm font-medium">Admin Username</label>
                        <input id="admin-username" name="username" type="text" placeholder="Username" value="{{ old('username') }}" required autofocus class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('username') border-destructive @enderror">
                        @error('username')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="admin-password" class="text-sm font-medium">Password</label>
                        <input id="admin-password" name="password" type="password" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('password') border-destructive @enderror">
                        @error('password')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    @if(!empty(config('services.turnstile.site_key')))
                        <div class="space-y-2">
                            <div class="turnstile-wrap">
                                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                            </div>
                            @error('cf-turnstile-response')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <button type="submit" data-up-loading-text="Signing in..." class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                        Sign In
                    </button>
                    <div class="text-center text-sm text-muted-foreground pt-2">
                        <a href="{{ route('login') }}" class="text-primary hover:underline">
                            Regular User Login
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-warning/10 border border-warning/20 rounded-lg p-4 text-sm">
            <p class="text-warning flex items-center gap-2">
                <i data-lucide="shield" class="h-4 w-4"></i>
                All admin login attempts are monitored and logged
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <script>
        window.renderTurnstiles = function () {
            if (!window.turnstile || typeof window.turnstile.render !== 'function') return;
            document.querySelectorAll('.cf-turnstile').forEach((el) => {
                if (el.dataset.rendered === '1') return;
                const sitekey = el.getAttribute('data-sitekey');
                if (!sitekey) return;
                try {
                    window.turnstile.render(el, { sitekey });
                    el.dataset.rendered = '1';
                } catch (e) {
                    // no-op
                }
            });
        };
        document.addEventListener('DOMContentLoaded', function () {
            window.renderTurnstiles();
            setTimeout(() => window.renderTurnstiles(), 500);
        });
        window.addEventListener('load', function () {
            window.renderTurnstiles();
        });
    </script>

</body>
</html>
