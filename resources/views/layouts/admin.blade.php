<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') - UniPrint</title>
    
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
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                        'card-hover': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                        'glow': '0 0 20px hsl(263 70% 50% / 0.3)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
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
            background: linear-gradient(135deg, hsl(16 90% 62%), hsl(16 100% 70%));
        }
        .gradient-admin {
            background: linear-gradient(135deg, hsl(0 84% 60%), hsl(0 100% 70%));
        }
        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .nav-link.active {
            background: hsl(0 84% 60%);
            color: hsl(0 0% 100%);
        }
        .nav-link:hover:not(.active) {
            background: hsl(240 4.8% 95.9%);
        }
    </style>
    
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-sidebar border-r border-border z-50 lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out" id="sidebar">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-border">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 gradient-admin rounded-xl flex items-center justify-center">
                            <i data-lucide="shield-check" class="h-6 w-6 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold gradient-admin bg-clip-text">UniPrint</h1>
                            <p class="text-xs text-muted-foreground">Admin Panel</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('admin.users') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.users') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="users" class="h-5 w-5"></i>
                        Users
                    </a>
                    
                    <a href="{{ route('admin.enterprises') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.enterprises') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="building-2" class="h-5 w-5"></i>
                        Enterprises
                    </a>
                    
                    <a href="{{ route('admin.orders') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.orders') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                        Orders
                    </a>
                    
                    <a href="{{ route('admin.services') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.services') || request()->routeIs('admin.products') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="package" class="h-5 w-5"></i>
                        Services
                    </a>
                    
                    <a href="{{ route('admin.reports') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('admin.reports') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="bar-chart-3" class="h-5 w-5"></i>
                        Reports
                    </a>
                </nav>
                
                <!-- User Info -->
                <div class="p-4 border-t border-border">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 gradient-admin rounded-full flex items-center justify-center">
                            <i data-lucide="user-check" class="h-4 w-4 text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">Admin User</p>
                            <p class="text-xs text-muted-foreground">System Administrator</p>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground hover:text-foreground hover:bg-secondary rounded-lg transition-smooth">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Mobile Sidebar Overlay -->
        <div class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="bg-card border-b border-border px-4 lg:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-secondary rounded-lg transition-smooth">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        
                        <!-- Page Title -->
                        <div>
                            <h1 class="text-xl lg:text-2xl font-bold">@yield('page-title', 'Admin Dashboard')</h1>
                            @hasSection('page-subtitle')
                                <p class="text-sm text-muted-foreground">@yield('page-subtitle')</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Header Actions -->
                    <div class="flex items-center gap-3">
                        @yield('header-actions')
                        
                        <!-- Notifications -->
                        <button class="relative p-2 hover:bg-secondary rounded-lg transition-smooth">
                            <i data-lucide="bell" class="h-5 w-5"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-destructive rounded-full"></span>
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-success/10 border border-success/20 text-success rounded-lg animate-slide-up">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="h-5 w-5"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg animate-slide-up">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="h-5 w-5"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg animate-slide-up">
                        <div class="flex items-start gap-2">
                            <i data-lucide="alert-triangle" class="h-5 w-5 mt-0.5"></i>
                            <div>
                                <p class="font-medium mb-1">Please fix the following errors:</p>
                                <ul class="text-sm space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.animate-slide-up');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !e.target.closest('[onclick="toggleSidebar()"]')) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
