<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UniPrint') - Printing Services Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/uniprint-ui.css') }}">
    @auth
        @php
            $layoutRoleType = Auth::user()->getUserRoleType();
        @endphp
        @if($layoutRoleType === 'customer')
            <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
        @endif
    @endauth
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 4px 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
        }

        .stat-card .display-6 {
            font-weight: 700;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #1e40af;
            border-color: #1e40af;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }

        .table {
            background-color: white;
        }

        .page-header {
            background: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary-color);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <a href="@yield('dashboard-route', '/')" class="navbar-brand d-flex align-items-center">
                        <i class="bi bi-printer-fill me-2"></i>
                        UniPrint
                    </a>
                </div>
                
                <nav class="nav flex-column mt-4">
                    @yield('sidebar')
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10">
                <!-- Top navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <div class="ms-auto d-flex align-items-center">
                            @auth
                            @php
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

                            <a href="{{ route('chat.index') }}" class="btn btn-outline-primary btn-sm me-3 position-relative">
                                <i class="bi bi-chat-dots me-1"></i>
                                Chat
                                @if($unreadChatCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}
                                    </span>
                                @endif
                            </a>

                            <span class="me-3 text-muted">
                                <i class="bi bi-person-circle me-2"></i>
                                {{ Auth::user()->username ?? Auth::user()->name ?? 'Account' }}
                                @php
                                    $roleType = Auth::user()->getUserRoleType();
                                @endphp
                                @if(!empty($roleType))
                                    <span class="badge bg-primary ms-2">{{ ucfirst($roleType) }}</span>
                                @endif
                            </span>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    Logout
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Login
                            </a>
                            @endauth
                        </div>
                    </div>
                </nav>

                <!-- Page content -->
                <div class="p-4">
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @auth
        @php
            $layoutRoleType2 = Auth::user()->getUserRoleType();
        @endphp
        @if($layoutRoleType2 === 'customer' && !request()->routeIs('chat.index'))
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/uniprint-ui.js') }}"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
