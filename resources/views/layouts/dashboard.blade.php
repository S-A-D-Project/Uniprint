<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - {{ system_brand_name() }}</title>
    
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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    
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

    <script src="{{ asset('js/uniprint-ui.js') }}"></script>

    @auth
        @php
            $layoutDashboardRoleType = Auth::user()->getUserRoleType();
            $unreadChatCount = 0;
            try {
                if (schema_has_table('conversations') && schema_has_table('chat_messages')) {
                    $uid = Auth::user()->user_id;
                    $conversationData = app('App\Services\TursoHttpService')->query('
                        SELECT conversation_id FROM conversations 
                        WHERE customer_id = ? OR business_id = ?
                    ', [$uid, $uid]);

                    if (!empty($conversationData)) {
                        $conversationIds = array_column($conversationData, 'conversation_id');
                        $placeholders = implode(',', array_fill(0, count($conversationIds), '?'));
                        $unreadChatData = app('App\Services\TursoHttpService')->query("SELECT COUNT(*) as count FROM chat_messages WHERE conversation_id IN ($placeholders) AND sender_id != ? AND is_read = 0", array_merge($conversationIds, [$uid]));
                        $unreadChatCount = (int) ($unreadChatData[0]['count'] ?? 0);
                    }
                }
            } catch (\Exception $e) {
                $unreadChatCount = 0;
            }
        @endphp
        @if($layoutDashboardRoleType === 'customer' && !request()->routeIs('chat.index'))
            <button type="button"
                    class="btn btn-primary position-fixed shadow-lg"
                    style="right: 24px; bottom: 24px; border-radius: 9999px; padding: 10px 16px; z-index: 1050;"
                    data-bs-toggle="offcanvas" data-bs-target="#customerChatWidget" aria-controls="customerChatWidget">
                <i class="bi bi-chat-dots-fill me-2"></i>
                Chat
                @if(!empty($unreadChatCount) && (int)$unreadChatCount > 0)
                    <span class="badge bg-danger ms-2">{{ (int)$unreadChatCount > 99 ? '99+' : (int)$unreadChatCount }}</span>
                @endif
            </button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="customerChatWidget" aria-labelledby="customerChatWidgetLabel" data-bs-backdrop="false" data-bs-scroll="true" style="--bs-offcanvas-width: min(420px, 95vw);">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="customerChatWidgetLabel"><i class="bi bi-chat-dots me-2 text-primary"></i>Chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div id="connectionStatus" class="connection-status connecting">
                        <i class="bi bi-wifi"></i> Connecting to chat server...
                    </div>

                    <div class="chat-container">
                        <div class="conversations-panel" id="conversationsPanel">
                            <div class="conversations-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Messages</h5>
                                    <small>Customer</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="startNewChatBtn" title="Start new chat">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="conversations-search">
                                <input type="text" class="form-control" id="searchConversations" placeholder="Search conversations...">
                            </div>
                            <div class="conversations-list" id="conversationsList">
                                <div class="text-center py-4">
                                    <div class="spinner mx-auto"></div>
                                    <p class="mt-2 text-muted">Loading conversations...</p>
                                </div>
                            </div>
                        </div>

                        <div class="chat-panel" id="chatPanel">
                            <div class="empty-state" id="emptyState">
                                <i class="bi bi-chat-text"></i>
                                <h5>Select a conversation</h5>
                                <p>Choose a conversation from the list to start chatting</p>
                            </div>
                            <div id="activeChat" style="display: none;">
                                @include('chat.partials.chat-header')
                                @include('chat.partials.chat-messages')
                                @include('chat.partials.chat-input')
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="businessListModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Start a new chat</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="businessList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                window.Laravel = window.Laravel || {};
                window.Laravel.user = {
                    id: '{{ auth()->user()->user_id }}',
                    name: '{{ auth()->user()->name }}',
                    role_type: '{{ auth()->user()->getUserRoleType() }}'
                };
                window.Laravel.pusher = {
                    key: '{{ config("broadcasting.connections.pusher.key") }}',
                    cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}'
                };
            </script>
            <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
            <script src="{{ asset('js/chat-app.js') }}"></script>
        @endif
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
