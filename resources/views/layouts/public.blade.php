<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'UniPrint') - Smart Printing Services for Baguio</title>
    
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
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    
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

    @auth
        @php
            $layoutPublicRoleType = Auth::user()->getUserRoleType();
            $unreadChatCount = 0;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('conversations') && \Illuminate\Support\Facades\Schema::hasTable('chat_messages')) {
                    $uid = Auth::user()->user_id;
                    $conversationIds = \Illuminate\Support\Facades\DB::table('conversations')
                        ->where('customer_id', $uid)
                        ->orWhere('business_id', $uid)
                        ->pluck('conversation_id');

                    if ($conversationIds->isNotEmpty()) {
                        $unreadChatCount = (int) \Illuminate\Support\Facades\DB::table('chat_messages')
                            ->whereIn('conversation_id', $conversationIds)
                            ->where('sender_id', '!=', $uid)
                            ->where('is_read', false)
                            ->count();
                    }
                }
            } catch (\Exception $e) {
                $unreadChatCount = 0;
            }
        @endphp
        @if($layoutPublicRoleType === 'customer' && !request()->routeIs('chat.index'))
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
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    
    <!-- Alpine.js for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
