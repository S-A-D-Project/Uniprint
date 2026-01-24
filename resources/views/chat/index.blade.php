@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <h3>
                <i class="bi bi-chat-dots-fill text-primary"></i>
                Real-time Chat
            </h3>
        </div>
    </div>

    <div id="connectionStatus" class="connection-status connecting">
        <i class="bi bi-wifi"></i> Connecting to chat server...
    </div>

    <div class="chat-container">
        <!-- Conversations List -->
        <div class="conversations-panel" id="conversationsPanel">
            <div class="conversations-header">
                <h5>
                    <i class="bi bi-chat-dots"></i> Messages
                </h5>
                <small>{{ auth()->user()->getUserRoleType() === 'customer' ? 'Customer' : 'Business Representative' }}</small>
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

        <!-- Chat Panel -->
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
</div>

<!-- Include styles -->
@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

<!-- Include scripts -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    // Make user data and Pusher config available to JavaScript
    window.Laravel = {
        user: {
            id: '{{ auth()->user()->user_id }}',
            name: '{{ auth()->user()->name }}',
            role_type: '{{ auth()->user()->getUserRoleType() }}'
        },
        pusher: {
            key: '{{ config("broadcasting.connections.pusher.key") }}',
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}'
        }
    };
</script>
<script src="{{ asset('js/chat-app.js') }}"></script>
@endpush
@endsection
