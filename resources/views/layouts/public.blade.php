<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', system_brand_name()) - {{ system_brand_tagline() }}</title>
    
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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    
    @stack('scripts')

    @auth
        @if(($layoutPublicRoleType ?? null) === 'customer')
            <div class="modal fade" id="userReportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('reports.store') }}" data-up-global-loader>
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Report</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="entity_type" id="userReportEntityType" value="">
                                <input type="hidden" name="enterprise_id" id="userReportEnterpriseId" value="">
                                <input type="hidden" name="service_id" id="userReportServiceId" value="">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Reason</label>
                                    <select name="reason" class="form-select" required>
                                        <option value="">Select a reason</option>
                                        <option value="Scam / Fraud">Scam / Fraud</option>
                                        <option value="Inappropriate content">Inappropriate content</option>
                                        <option value="Misleading information">Misleading information</option>
                                        <option value="Poor service">Poor service</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Details (optional)</label>
                                    <textarea name="description" class="form-control" rows="3" maxlength="2000" placeholder="Provide more context..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger" data-up-button-loader>Submit Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="systemFeedbackModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('system-feedback.store') }}" data-up-global-loader>
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">System Feedback</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Category</label>
                                    <select name="category" class="form-select" required>
                                        <option value="general">General</option>
                                        <option value="bug">Bug / Issue</option>
                                        <option value="ui">UI / UX</option>
                                        <option value="performance">Performance</option>
                                        <option value="feature_request">Feature Request</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rating (optional)</label>
                                    <select name="rating" class="form-select">
                                        <option value="">No rating</option>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Good</option>
                                        <option value="3">3 - Okay</option>
                                        <option value="2">2 - Needs improvement</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Subject</label>
                                    <input name="subject" class="form-control" maxlength="255" required placeholder="Short summary" />
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Message</label>
                                    <textarea name="message" class="form-control" rows="4" maxlength="5000" required placeholder="Tell us what you think and what we can improve..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" data-up-button-loader>Submit Feedback</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <script>
        document.addEventListener('click', async function (e) {
            const link = e.target.closest('a.js-open-enterprise-chat');
            if (!link) return;

            const enterpriseId = link.getAttribute('data-enterprise-id');
            if (!enterpriseId) return;

            if (window.UniPrintChat && typeof window.UniPrintChat.openEnterpriseChat === 'function') {
                e.preventDefault();
                try {
                    await window.UniPrintChat.openEnterpriseChat(enterpriseId);
                } catch (err) {
                    window.location.href = link.getAttribute('href');
                }
            }
        });
    </script>

    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-up-report]');
            if (!btn) return;

            const modalEl = document.getElementById('userReportModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            const entityType = btn.getAttribute('data-entity-type') || '';
            const enterpriseId = btn.getAttribute('data-enterprise-id') || '';
            const serviceId = btn.getAttribute('data-service-id') || '';

            const et = document.getElementById('userReportEntityType');
            const eid = document.getElementById('userReportEnterpriseId');
            const sid = document.getElementById('userReportServiceId');
            if (et) et.value = entityType;
            if (eid) eid.value = enterpriseId;
            if (sid) sid.value = serviceId;

            const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            inst.show();
        });
    </script>

    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-up-feedback]');
            if (!btn) return;

            const modalEl = document.getElementById('systemFeedbackModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            inst.show();
        });
    </script>
</body>
</html>
