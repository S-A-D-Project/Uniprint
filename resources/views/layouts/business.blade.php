<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Business Dashboard') - {{ system_brand_name() }}</title>
    
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

    <!-- Bootstrap CSS (used by some business components like modals/tooltips) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
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

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    
    <!-- Custom CSS -->
    <style>
        .gradient-primary {
            background: linear-gradient(135deg, hsl(263 70% 50%), hsl(263 85% 65%));
        }
        .gradient-accent {
            background: linear-gradient(135deg, hsl(16 90% 62%), hsl(16 100% 70%));
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
            background: hsl(263 70% 50%);
            color: hsl(0 0% 100%);
        }
        .nav-link:hover:not(.active) {
            background: hsl(240 4.8% 95.9%);
        }
    </style>
    
    <!-- Lucide Icons -->
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
                        @php
                            $systemBrandLogoUrl = system_brand_logo_url();
                        @endphp
                        @if($systemBrandLogoUrl)
                            <img src="{{ $systemBrandLogoUrl }}" alt="{{ system_brand_name() }}" class="w-10 h-10 rounded-xl object-cover border border-border" />
                        @else
                            <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
                                <i data-lucide="store" class="h-6 w-6 text-white"></i>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-xl font-bold gradient-primary bg-clip-text">{{ system_brand_name() }}</h1>
                            <p class="text-xs text-muted-foreground">Business Portal</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('business.dashboard') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.dashboard') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('business.orders.index') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.orders.*') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="shopping-bag" class="h-5 w-5"></i>
                        Orders
                        @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                            <span class="ml-auto bg-warning text-warning-foreground text-xs px-2 py-1 rounded-full">{{ $pendingOrdersCount }}</span>
                        @endif
                    </a>
                    
                    <a href="{{ route('business.services.index') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.services.*') || request()->routeIs('business.customizations.*') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="package" class="h-5 w-5"></i>
                        Services
                    </a>
                    
                    <a href="{{ route('business.pricing.index') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.pricing.*') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="percent" class="h-5 w-5"></i>
                        Pricing Rules
                    </a>
                    
                    <a href="{{ route('business.chat') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.chat') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="message-circle" class="h-5 w-5"></i>
                        Customer Chat
                        <span id="unread-messages-badge" class="ml-auto bg-primary text-primary-foreground text-xs px-2 py-1 rounded-full hidden">0</span>
                    </a>

                    <a href="{{ route('business.settings') }}" 
                       class="nav-link flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-smooth {{ request()->routeIs('business.settings') || request()->routeIs('business.settings.*') ? 'active' : 'text-sidebar-foreground' }}">
                        <i data-lucide="settings" class="h-5 w-5"></i>
                        Settings
                    </a>
                </nav>
                
                <!-- User Info -->
                <div class="p-4 border-t border-border">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="h-4 w-4 text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ $userName ?? 'Business User' }}</p>
                            <p class="text-xs text-muted-foreground">{{ $enterprise->name ?? 'Enterprise' }}</p>
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
                            <h1 class="text-xl lg:text-2xl font-bold">@yield('page-title', 'Dashboard')</h1>
                            @hasSection('page-subtitle')
                                <p class="text-sm text-muted-foreground">@yield('page-subtitle')</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Header Actions -->
                    <div class="flex items-center gap-3">
                        @yield('header-actions')
                        
                        <!-- Notifications -->
                        <button type="button" class="relative p-2 hover:bg-secondary rounded-lg transition-smooth" data-bs-toggle="modal" data-bs-target="#businessNotificationsModal">
                            <i data-lucide="bell" class="h-5 w-5"></i>
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-destructive text-destructive-foreground text-[11px] leading-[18px] rounded-full text-center" id="businessNotificationsBadge" @if(empty($unreadNotificationsCount)) style="display:none;" @endif>
                                {{ !empty($unreadNotificationsCount) ? ($unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount) : '' }}
                            </span>
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

    <div class="modal fade" id="businessNotificationsModal" tabindex="-1" aria-hidden="true" data-notifications-url="{{ route('business.notifications') }}" data-notification-read-url-template="{{ route('business.notifications.read', ['id' => '___ID___']) }}">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" style="max-width: 560px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2"><i class="bi bi-bell"></i> Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="businessNotificationsEmpty" class="p-5 text-center" style="display:none;">
                        <div class="text-muted small">No notifications</div>
                    </div>
                    <div id="businessNotificationsList"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="businessNotificationsRefresh">Refresh</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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

    <script>
        (function(){
            const modalEl = document.getElementById('businessNotificationsModal');
            if(!modalEl) return;

            const listEl = document.getElementById('businessNotificationsList');
            const loadingEl = document.getElementById('businessNotificationsLoading');
            const emptyEl = document.getElementById('businessNotificationsEmpty');
            const refreshBtn = document.getElementById('businessNotificationsRefresh');

            const badgeEl = document.getElementById('businessNotificationsBadge');
            const notificationsUrl = modalEl.getAttribute('data-notifications-url');
            const readTpl = modalEl.getAttribute('data-notification-read-url-template');

            const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function iconFor(type){
                if(type === 'deadline_warning') return 'exclamation-triangle';
                if(type === 'status_change') return 'bell';
                if(type === 'file_upload') return 'file-earmark';
                return 'chat-dots';
            }

            function formatTime(ts){
                if(!ts) return '';
                const d = new Date(ts);
                if(Number.isNaN(d.getTime())) return String(ts);
                return d.toLocaleString();
            }

            function setBadge(count){
                if(!badgeEl) return;
                if(typeof count !== 'number' || count <= 0){
                    badgeEl.style.display = 'none';
                    return;
                }
                badgeEl.style.display = 'inline-block';
                badgeEl.textContent = count > 99 ? '99+' : String(count);
            }

            async function markRead(id, btn){
                if(!readTpl) return;
                const url = readTpl.replace('___ID___', id);
                try{
                    const res = await fetch(url, {
                        method:'POST',
                        credentials: 'same-origin',
                        headers:{
                            'Accept':'application/json',
                            'Content-Type':'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken() ? {'X-CSRF-TOKEN': csrfToken()} : {})
                        },
                        body: JSON.stringify({})
                    });
                    const data = await res.json();
                    if(data && data.success){
                        if(btn){
                            btn.disabled = true;
                            btn.textContent = 'Read';
                        }
                        await load();
                    }
                }catch(_){
                }
            }

            function render(items){
                if(!listEl) return;
                listEl.innerHTML = '';

                if(!items || !items.length){
                    if(emptyEl) emptyEl.style.display = 'block';
                    return;
                }
                if(emptyEl) emptyEl.style.display = 'none';

                items.forEach(n => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'p-3 border-bottom d-flex gap-3 align-items-start';

                    const icon = document.createElement('div');
                    icon.className = 'rounded-circle d-flex align-items-center justify-content-center flex-shrink-0';
                    icon.style.width = '36px';
                    icon.style.height = '36px';
                    icon.style.background = 'rgba(99,102,241,0.10)';
                    icon.innerHTML = `<i class="bi bi-${iconFor(n.notification_type)}"></i>`;

                    const body = document.createElement('div');
                    body.className = 'flex-grow-1';
                    const viewUrl = n.purchase_order_id ? `{{ route('business.orders.details', ['id' => '___ID___']) }}`.replace('___ID___', n.purchase_order_id) : '';
                    body.innerHTML = `
                        <div class="d-flex justify-content-between gap-2">
                            <div class="fw-semibold">${(n.title||'')}</div>
                            <div class="text-muted small">${formatTime(n.created_at)}</div>
                        </div>
                        <div class="text-muted small mt-1">${(n.message||'')}</div>
                        ${viewUrl ? `<div class=\"mt-2\"><a class=\"text-primary small\" href=\"${viewUrl}\">View order →</a></div>` : ''}
                    `;

                    const actions = document.createElement('div');
                    actions.className = 'flex-shrink-0';
                    if(!n.is_read){
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-sm btn-outline-primary';
                        btn.textContent = 'Mark read';
                        btn.addEventListener('click', () => markRead(n.notification_id, btn));
                        actions.appendChild(btn);
                    }

                    wrapper.appendChild(icon);
                    wrapper.appendChild(body);
                    wrapper.appendChild(actions);
                    listEl.appendChild(wrapper);
                });
            }

            async function load(){
                if(!notificationsUrl) return;
                if(loadingEl) loadingEl.style.display = 'block';
                if(emptyEl) emptyEl.style.display = 'none';
                try{
                    const res = await fetch(notificationsUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if(data && data.success){
                        render(Array.isArray(data.notifications) ? data.notifications : []);
                        setBadge(typeof data.unread_count === 'number' ? data.unread_count : null);
                    }
                }catch(_){
                }finally{
                    if(loadingEl) loadingEl.style.display = 'none';
                }
            }

            modalEl.addEventListener('show.bs.modal', load);
            if(refreshBtn) refreshBtn.addEventListener('click', load);
        })();
    </script>

    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
