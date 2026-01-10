<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') - UniPrint</title>
    
    <!-- Preload Critical Assets -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preload" href="https://unpkg.com/lucide@latest" as="script">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Custom TailwindCSS Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Admin-specific color palette
                        background: 'hsl(0 0% 100%)',
                        foreground: 'hsl(240 10% 3.9%)',
                        card: 'hsl(0 0% 100%)',
                        'card-foreground': 'hsl(240 10% 3.9%)',
                        popover: 'hsl(0 0% 100%)',
                        'popover-foreground': 'hsl(240 10% 3.9%)',
                        
                        // Admin primary color (red theme)
                        primary: 'hsl(0 84% 60%)',
                        'primary-foreground': 'hsl(0 0% 100%)',
                        'primary-hover': 'hsl(0 84% 55%)',
                        
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
                        ring: 'hsl(0 84% 60%)',
                        
                        sidebar: 'hsl(240 5.9% 10%)',
                        'sidebar-foreground': 'hsl(0 0% 98%)',
                        'sidebar-hover': 'hsl(240 5.9% 15%)',
                    },
                    borderRadius: {
                        DEFAULT: '0.75rem',
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                        'card-hover': '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                        'glow': '0 0 20px hsl(0 84% 60% / 0.3)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                    }
                }
            }
        }
    </script>

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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar: #f8f9fa;
            --sidebar-foreground: #1a1a1a;
            --sidebar-hover: #e9ecef;
            --card-bg: #ffffff;
            --card-border: #e9ecef;
        }
        
        /* Text Colors */
        body {
            color: #1a1a1a;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        /* Admin Gradients */
        .gradient-admin {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        .gradient-admin-dark {
            background: linear-gradient(135deg, hsl(0 84% 50%), hsl(0 84% 60%));
        }
        
        /* Smooth Transitions */
        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Text Gradient Clip */
        .bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Navigation Links */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--sidebar-foreground);
            text-decoration: none;
        }

        .nav-link:hover:not(.active) {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-foreground);
        }

        .nav-link i {
            opacity: 0.8;
        }

        .nav-link.active {
            background: hsl(0 84% 60%);
            color: hsl(0 0% 100%);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .nav-link.active i {
            opacity: 1;
        }
        
        /* Tab Styles */
        .tab-button {
            position: relative;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: hsl(240 3.8% 46.1%);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 2px solid transparent;
        }
        
        .tab-button:hover:not(.active) {
            color: hsl(240 10% 3.9%);
            background: hsl(240 4.8% 95.9%);
        }
        
        .tab-button.active {
            color: hsl(0 84% 60%);
            border-bottom-color: hsl(0 84% 60%);
        }
        
        /* Breadcrumb Styles */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: hsl(240 3.8% 46.1%);
        }
        
        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .breadcrumb-item a {
            color: hsl(240 3.8% 46.1%);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .breadcrumb-item a:hover {
            color: hsl(0 84% 60%);
        }
        
        .breadcrumb-item.active {
            color: hsl(240 10% 3.9%);
            font-weight: 500;
        }
        
        /* Sidebar Scrollbar */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: hsl(240 5.9% 20%);
            border-radius: 3px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: hsl(240 5.9% 25%);
        }
        
        /* Content Area */
        .content-wrapper {
            min-height: calc(100vh - 4rem);
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-full-width {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans antialiased text-foreground">
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 shadow-sm z-50 lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out no-print" id="sidebar">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-sidebar-hover">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="shield-check" class="h-6 w-6 text-primary"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">UniPrint</h1>
                            <p class="text-xs text-gray-500">Admin Panel</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto sidebar-nav">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       aria-label="Dashboard">
                        <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('admin.users') }}" 
                       class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                       aria-label="Users Management">
                        <i data-lucide="users" class="h-5 w-5"></i>
                        <span>Users</span>
                    </a>
                    
                    <a href="{{ route('admin.enterprises') }}" 
                       class="nav-link {{ request()->routeIs('admin.enterprises') ? 'active' : '' }}"
                       aria-label="Enterprises Management">
                        <i data-lucide="building-2" class="h-5 w-5"></i>
                        <span>Enterprises</span>
                    </a>
                    
                    <a href="{{ route('admin.orders') }}" 
                       class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}"
                       aria-label="Orders Management">
                        <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                        <span>Orders</span>
                    </a>
                    
                    <a href="{{ route('admin.services') }}" 
                       class="nav-link {{ request()->routeIs('admin.services') || request()->routeIs('admin.products') ? 'active' : '' }}"
                       aria-label="Services Management">
                        <i data-lucide="package" class="h-5 w-5"></i>
                        <span>Services</span>
                    </a>
                    
                    <a href="{{ route('admin.reports') }}" 
                       class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
                       aria-label="Reports">
                        <i data-lucide="bar-chart-3" class="h-5 w-5"></i>
                        <span>Reports</span>
                    </a>
                    
                    <div class="pt-4 mt-4 border-t border-sidebar-hover">
                        <p class="px-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            System
                        </p>
                        
                        <a href="{{ route('admin.settings') }}" 
                           class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                           aria-label="Settings">
                            <i data-lucide="settings" class="h-5 w-5"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </nav>
                
                <!-- User Info -->
                <div class="p-4 border-t border-sidebar-hover">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                            <i data-lucide="user-check" class="h-4 w-4 text-primary"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ Auth::user()->name ?? 'Admin User' }}
                            </p>
                            <p class="text-xs text-gray-500">System Administrator</p>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-smooth">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Sidebar Overlay -->
        <div class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden no-print" id="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="bg-card border-b border-border px-4 lg:px-6 py-4 no-print" role="banner">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button onclick="toggleSidebar()" 
                                class="lg:hidden p-2 hover:bg-secondary rounded-lg transition-smooth"
                                aria-label="Toggle Sidebar">
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
                        <button class="relative p-2 hover:bg-secondary rounded-lg transition-smooth" aria-label="Notifications">
                            <i data-lucide="bell" class="h-5 w-5"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-destructive rounded-full"></span>
                        </button>
                        
                        <!-- Quick Actions -->
                        <button class="p-2 hover:bg-secondary rounded-lg transition-smooth" aria-label="Quick Actions">
                            <i data-lucide="more-vertical" class="h-5 w-5"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Breadcrumb Navigation -->
                @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
                    <nav class="mt-4 breadcrumb" aria-label="Breadcrumb">
                        @if(isset($breadcrumbs))
                            @foreach($breadcrumbs as $index => $crumb)
                                <div class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if($loop->last)
                                        <span>{{ $crumb['label'] }}</span>
                                    @else
                                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            @yield('breadcrumbs')
                        @endif
                    </nav>
                @endif
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto content-wrapper" role="main">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-success/10 border border-success/20 text-success rounded-lg animate-slide-up" role="alert">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="h-5 w-5"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg animate-slide-up" role="alert">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="h-5 w-5"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg animate-slide-up" role="alert">
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
            
            <!-- Footer -->
            <footer class="bg-card border-t border-border px-4 lg:px-6 py-4 no-print" role="contentinfo">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted-foreground">
                    <p>&copy; {{ date('Y') }} UniPrint. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="hover:text-foreground transition-smooth">Documentation</a>
                        <a href="#" class="hover:text-foreground transition-smooth">Support</a>
                        <a href="#" class="hover:text-foreground transition-smooth">Privacy</a>
                    </div>
                </div>
            </footer>
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
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // ESC key to close sidebar on mobile
            if (e.key === 'Escape' && window.innerWidth < 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    </script>

    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
