<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - UniPrint</title>
    
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

    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    
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
        .bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-accent/5 flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <div class="flex items-center justify-center mb-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-primary hover:opacity-80 transition-smooth">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                <span class="text-sm">Back to Home</span>
            </a>
        </div>

        <div class="text-center space-y-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-4">
                <i data-lucide="store" class="h-8 w-8 text-primary"></i>
                <span class="text-2xl font-bold gradient-primary bg-clip-text text-transparent">
                    UniPrint
                </span>
            </a>
            <h1 class="text-3xl font-bold">Create Account</h1>
            <p class="text-muted-foreground">Join UniPrint to start ordering prints</p>
        </div>

        <div class="bg-card border border-border rounded-xl shadow-card-hover p-6">
            <form method="POST" action="{{ route('register') }}" data-up-button-loader>
                @csrf
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('name') border-destructive @enderror">
                        @error('name')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('email') border-destructive @enderror">
                        @error('email')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="space-y-2">
                        <label for="username" class="text-sm font-medium">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('username') border-destructive @enderror">
                        @error('username')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium">Password</label>
                        <input id="password" name="password" type="password" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('password') border-destructive @enderror">
                        @error('password')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-medium">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-start gap-3 text-sm">
                            <input
                                id="terms_accepted"
                                name="terms_accepted"
                                type="checkbox"
                                value="1"
                                {{ old('terms_accepted') ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 rounded border-input text-primary focus:ring-ring @error('terms_accepted') border-destructive @enderror"
                                required
                            >
                            <span class="text-muted-foreground">
                                I agree to the
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="text-primary hover:underline">Terms &amp; Conditions</a>
                            </span>
                        </label>
                        @error('terms_accepted')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <button type="submit" data-up-loading-text="Creating account..." class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                        Create Account
                    </button>
                    
                    <div class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-primary hover:underline">
                            Sign in here
                        </a>
                    </div>
                </div>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-border"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-card text-muted-foreground">Or sign up with</span>
                </div>
            </div>

            <!-- Social Signup Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('auth.google') }}" data-oauth-base="{{ route('auth.google') }}" class="oauth-link flex items-center justify-center gap-2 px-4 py-2 border border-input rounded-md hover:bg-secondary transition-smooth">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span class="text-sm font-medium">Google</span>
                </a>
                <a href="{{ route('auth.facebook') }}" data-oauth-base="{{ route('auth.facebook') }}" class="oauth-link flex items-center justify-center gap-2 px-4 py-2 border border-input rounded-md hover:bg-secondary transition-smooth">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="#1877F2" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="text-sm font-medium">Facebook</span>
                </a>
            </div>
        </div>

        <p class="text-center text-sm text-muted-foreground">
            By creating an account, you agree to our <a href="{{ route('terms') }}" class="text-primary hover:underline">Terms &amp; Conditions</a> and Privacy Policy
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>

    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    <script>
        function getSelectedRoleType() {
            const checked = document.querySelector('input[name="role_type"]:checked');
            const value = checked ? checked.value : 'customer';
            return value === 'business' ? 'business' : 'customer';
        }

        document.querySelectorAll('a.oauth-link').forEach((a) => {
            a.addEventListener('click', () => {
                const base = a.getAttribute('data-oauth-base') || a.getAttribute('href');
                const roleType = getSelectedRoleType();
                a.setAttribute('href', `${base}?role_type=${encodeURIComponent(roleType)}`);
            });
        });
    </script>
</body>
</html>
