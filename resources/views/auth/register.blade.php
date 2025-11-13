<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - UniPrint</title>
    
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
            <h1 class="text-3xl font-bold">Create Account</h1>
            <p class="text-muted-foreground">Join UniPrint to start ordering prints</p>
        </div>

        <div class="bg-card border border-border rounded-xl shadow-card-hover p-6">
            <form method="POST" action="{{ route('register') }}">
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
                    
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
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
        </div>

        <p class="text-center text-sm text-muted-foreground">
            By creating an account, you agree to our Terms of Service and Privacy Policy
        </p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
