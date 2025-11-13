<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - UniPrint</title>
    
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
            <h1 class="text-3xl font-bold">Welcome</h1>
            <p class="text-muted-foreground">Sign in or create your account</p>
        </div>

        <div class="bg-card border border-border rounded-xl shadow-card-hover" x-data="{ activeTab: 'login' }">
            <!-- Tabs -->
            <div class="grid grid-cols-2 gap-0 border-b border-border p-4">
                <button @click="activeTab = 'login'" :class="activeTab === 'login' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground'" class="px-4 py-2 text-sm font-medium rounded-md transition-smooth">
                    Sign In
                </button>
                <button @click="activeTab = 'signup'" :class="activeTab === 'signup' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground'" class="px-4 py-2 text-sm font-medium rounded-md transition-smooth">
                    Sign Up
                </button>
            </div>

            <!-- Login Tab -->
            <div x-show="activeTab === 'login'" class="p-6">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label for="login-username" class="text-sm font-medium">Username or Email</label>
                            <input id="login-username" name="username" type="text" placeholder="Enter your username" value="{{ old('username') }}" required autofocus class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('username') border-destructive @enderror">
                            @error('username')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="login-password" class="text-sm font-medium">Password</label>
                            <input id="login-password" name="password" type="password" placeholder="••••••••" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('password') border-destructive @enderror">
                            @error('password')
                                <p class="text-sm text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>

            <!-- Signup Tab -->
            <div x-show="activeTab === 'signup'" class="p-6">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Account Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center justify-center px-4 py-2 border-2 border-input rounded-md cursor-pointer transition-smooth hover:border-primary">
                                    <input type="radio" name="role_type" value="customer" class="sr-only peer" checked>
                                    <span class="peer-checked:font-semibold peer-checked:text-primary">Customer</span>
                                </label>
                                <label class="flex items-center justify-center px-4 py-2 border-2 border-input rounded-md cursor-pointer transition-smooth hover:border-primary">
                                    <input type="radio" name="role_type" value="business" class="sr-only peer">
                                    <span class="peer-checked:font-semibold peer-checked:text-primary">Business</span>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="signup-name" class="text-sm font-medium">Full Name</label>
                            <input id="signup-name" name="name" type="text" placeholder="John Doe" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label for="signup-email" class="text-sm font-medium">Email</label>
                            <input id="signup-email" name="email" type="email" placeholder="you@example.com" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label for="signup-username" class="text-sm font-medium">Username</label>
                            <input id="signup-username" name="username" type="text" placeholder="johndoe" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label for="signup-password" class="text-sm font-medium">Password</label>
                            <input id="signup-password" name="password" type="password" placeholder="••••••••" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label for="signup-password-confirm" class="text-sm font-medium">Confirm Password</label>
                            <input id="signup-password-confirm" name="password_confirmation" type="password" placeholder="••••••••" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Sign Up
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center text-sm text-muted-foreground">
            By continuing, you agree to our Terms of Service and Privacy Policy
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
