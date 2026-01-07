<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - UniPrint</title>
    
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
                        popover: 'hsl(0 0% 100%)',
                        'popover-foreground': 'hsl(240 10% 3.9%)',
                        primary: 'hsl(263 70% 50%)',
                        'primary-foreground': 'hsl(0 0% 100%)',
                        secondary: 'hsl(240 4.8% 95.9%)',
                        'secondary-foreground': 'hsl(240 5.9% 10%)',
                        muted: 'hsl(240 4.8% 95.9%)',
                        'muted-foreground': 'hsl(240 3.8% 46.1%)',
                        accent: 'hsl(16 90% 62%)',
                        'accent-foreground': 'hsl(0 0% 100%)',
                        success: 'hsl(142 76% 36%)',
                        'success-foreground': 'hsl(0 0% 100%)',
                        warning: 'hsl(38 92% 50%)',
                        'warning-foreground': 'hsl(0 0% 100%)',
                        destructive: 'hsl(0 84.2% 60.2%)',
                        'destructive-foreground': 'hsl(0 0% 100%)',
                        border: 'hsl(240 5.9% 90%)',
                        input: 'hsl(240 5.9% 90%)',
                        ring: 'hsl(263 70% 50%)',
                        sidebar: 'hsl(240 5.9% 96%)',
                        'sidebar-foreground': 'hsl(240 5.9% 10%)',
                    },
                    borderRadius: {
                        DEFAULT: '0.75rem',
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
        .gradient-accent {
            background: linear-gradient(135deg, hsl(16 90% 62%), hsl(16 95% 70%));
        }
        .gradient-hero {
            background: linear-gradient(135deg, hsl(263 70% 50%) 0%, hsl(220 70% 50%) 100%);
        }
        .shadow-card {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
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
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        h1 { font-size: 2.25rem; }
        h2 { font-size: 1.875rem; }
        h3 { font-size: 1.5rem; }
        @media (min-width: 768px) {
            h1 { font-size: 3rem; }
            h2 { font-size: 2.25rem; }
            h3 { font-size: 1.875rem; }
        }
        @media (min-width: 1024px) {
            h1 { font-size: 3.75rem; }
            h2 { font-size: 3rem; }
        }
    </style>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    @stack('styles')
</head>
<body class="min-h-screen bg-background text-foreground">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden md:flex md:flex-col w-64 bg-sidebar border-r border-border">
            @yield('sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-card border-b border-border px-4 flex items-center justify-between">
                @yield('header')
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>

    <!-- Include Help Widget -->
    @include('components.help-widget')

    @stack('scripts')
</body>
</html>
