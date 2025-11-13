<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'UniPrint') - Smart Printing Services for Baguio</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom TailwindCSS Configuration -->
    <script>
        tailwind.config = {
            theme: {
                container: {
                    center: true,
                    padding: '2rem',
                    screens: {
                        '2xl': '1400px',
                    }
                },
                extend: {
                    colors: {
                        background: 'hsl(0 0% 100%)',
                        foreground: 'hsl(240 10% 3.9%)',
                        card: 'hsl(0 0% 100%)',
                        'card-foreground': 'hsl(240 10% 3.9%)',
                        popover: 'hsl(0 0% 100%)',
                        'popover-foreground': 'hsl(240 10% 3.9%)',
                        primary: {
                            DEFAULT: 'hsl(263 70% 50%)',
                            foreground: 'hsl(0 0% 100%)',
                            glow: 'hsl(263 85% 65%)',
                        },
                        secondary: {
                            DEFAULT: 'hsl(240 4.8% 95.9%)',
                            foreground: 'hsl(240 5.9% 10%)',
                        },
                        muted: {
                            DEFAULT: 'hsl(240 4.8% 95.9%)',
                            foreground: 'hsl(240 3.8% 46.1%)',
                        },
                        accent: {
                            DEFAULT: 'hsl(16 90% 62%)',
                            foreground: 'hsl(0 0% 100%)',
                            glow: 'hsl(16 95% 70%)',
                        },
                        success: {
                            DEFAULT: 'hsl(142 76% 36%)',
                            foreground: 'hsl(0 0% 100%)',
                        },
                        warning: {
                            DEFAULT: 'hsl(38 92% 50%)',
                            foreground: 'hsl(0 0% 100%)',
                        },
                        destructive: {
                            DEFAULT: 'hsl(0 84.2% 60.2%)',
                            foreground: 'hsl(0 0% 100%)',
                        },
                        border: 'hsl(240 5.9% 90%)',
                        input: 'hsl(240 5.9% 90%)',
                        ring: 'hsl(263 70% 50%)',
                    },
                    borderRadius: {
                        lg: '0.75rem',
                        md: 'calc(0.75rem - 2px)',
                        sm: 'calc(0.75rem - 4px)',
                    }
                }
            }
        }
    </script>
    
    <!-- UniPrint Design System - Complete CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    @stack('styles')
</head>
<body class="min-h-screen bg-background text-foreground">
    <!-- Header -->
    @include('partials.header')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- Modern Chatbot Component -->
    @include('components.chatbot-lucide')
    
    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="fixed bottom-4 right-4 z-50 p-4 bg-success text-success-foreground rounded-lg shadow-card-hover transition-smooth">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="fixed bottom-4 right-4 z-50 p-4 bg-destructive text-destructive-foreground rounded-lg shadow-card-hover transition-smooth">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-circle" class="h-5 w-5"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    
    <!-- Alpine.js for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>
