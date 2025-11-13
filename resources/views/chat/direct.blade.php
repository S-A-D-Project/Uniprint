@extends('layouts.app')

@section('title', 'Direct Chat - Sarah & Business')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-blue-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Chat Header -->
        <div class="bg-white rounded-t-2xl shadow-lg border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        {{ substr($otherUser->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Direct Chat with {{ $otherUser->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <div id="online-indicator" class="w-3 h-3 rounded-full bg-green-400"></div>
                            <span id="online-status" class="text-sm text-gray-600">Online</span>
                            <span id="typing-indicator" class="text-sm text-purple-600 italic hidden ml-2">typing...</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div id="connection-status" class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        Connecting...
                    </div>
                    <button id="refresh-chat" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="bg-white shadow-lg" style="height: 500px;">
            <div id="messages-container" class="h-full overflow-y-auto p-6 space-y-4">
                <div id="welcome-message" class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-100 to-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Direct Chat Started</h3>
                    <p class="text-gray-600">You're now chatting directly with {{ $otherUser->name }}</p>
                </div>
            </div>
        </div>

        <!-- Message Input Area -->
        <div class="bg-white rounded-b-2xl shadow-lg border-t border-gray-200 p-6">
            <div class="flex gap-4">
                <div class="flex-1">
                    <textarea 
                        id="message-input" 
                        placeholder="Type your message to {{ $otherUser->name }}..." 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                        rows="2"
                        maxlength="1000"></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <small class="text-gray-500">Press Enter to send, Shift+Enter for new line</small>
                        <small class="text-gray-500"><span id="char-count">0</span>/1000</small>
                    </div>
                </div>
                <button 
                    id="send-button" 
                    class="bg-gradient-to-r from-purple-500 to-blue-500 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-blue-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Pusher CDN v7.0 -->
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
class DirectChat {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.conversationId = '{{ $conversation->conversation_id }}';
        this.currentUserId = '{{ auth()->id() }}';
        this.currentUserName = '{{ auth()->user()->name }}';
        this.otherUserId = '{{ $otherUser->user_id }}';
        this.otherUserName = '{{ $otherUser->name }}';
        this.messages = [];
        this.typingTimeout = null;
        this.retryAttempts = 0;
        this.maxRetries = 3;
        
        this.init();
    }

    init() {
        console.log('[DirectChat] Initializing direct chat between users...');
        console.log('[DirectChat] Conversation ID:', this.conversationId);
        
        this.setupPusher();
        this.setupEventListeners();
        this.loadMessages();
        this.updateOnlineStatus();
        
        // Update online status every 30 seconds
        setInterval(() => this.updateOnlineStatus(), 30000);
    }

    setupPusher() {
        try {
            const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
            const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
            
            console.log('[DirectChat] Pusher Config:', { key: pusherKey, cluster: pusherCluster });
            
            if (!pusherKey || pusherKey === 'your_app_key') {
                console.error('[Pusher] Invalid configuration');
                this.updateConnectionStatus('error', 'Invalid Pusher configuration');
                return;
            }
            
            this.pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                enabledTransports: ['ws', 'wss'],
                forceTLS: true,
                authEndpoint: '/api/chat/pusher/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }
            });

            this.pusher.connection.bind('connected', () => {
                console.log('[Pusher] Connected successfully');
                this.updateConnectionStatus('connected', 'Connected');
                this.subscribeToConversation();
                this.retryAttempts = 0;
            });

            this.pusher.connection.bind('disconnected', () => {
                console.log('[Pusher] Disconnected');
                this.updateConnectionStatus('disconnected', 'Disconnected');
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('[Pusher] Connection error:', error);
                this.updateConnectionStatus('error', 'Connection Error');
                this.handleConnectionError();
            });

        } catch (error) {
            console.error('[Pusher] Setup error:', error);
            this.updateConnectionStatus('error', 'Setup Error');
        }
    }

    handleConnectionError() {
        if (this.retryAttempts < this.maxRetries) {
            this.retryAttempts++;
            console.log(`[Pusher] Retrying connection (${this.retryAttempts}/${this.maxRetries})...`);
            setTimeout(() => {
                this.setupPusher();
            }, 2000 * this.retryAttempts);
        }
    }

    updateConnectionStatus(status, message) {
        const statusElement = document.getElementById('connection-status');
        if (!statusElement) return;

        statusElement.className = 'px-3 py-1 rounded-full text-sm font-medium';
        
        switch (status) {
            case 'connected':
                statusElement.className += ' bg-green-100 text-green-800';
                break;
            case 'disconnected':
                statusElement.className += ' bg-yellow-100 text-yellow-800';
                break;
            case 'error':
                statusElement.className += ' bg-red-100 text-red-800';
                break;
            default:
                statusElement.className += ' bg-yellow-100 text-yellow-800';
        }
        
        statusElement.textContent = message;
    }

    subscribeToConversation() {
        if (!this.pusher) return;

        const channelName = `conversation.${this.conversationId}`;
        
        try {
            this.channel = this.pusher.subscribe(channelName);

            this.channel.bind('new-message', (data) => {
                console.log('[Pusher] New message received:', data);
                if (data.sender_id !== this.currentUserId) {
                    this.handleNewMessage(data);
                }
            });

            this.channel.bind('user-typing', (data) => {
                console.log('[Pusher] Typing indicator:', data);
                if (data.user_id !== this.currentUserId) {
                    this.handleTypingIndicator(data);
                }
            });

            console.log('[Pusher] Subscribed to channel:', channelName);

        } catch (error) {
            console.error('[Pusher] Subscription error:', error);
        }
    }

    async loadMessages() {
        try {
            console.log('[DirectChat] Loading messages...');
            
            const response = await fetch(`/api/chat/conversations/${this.conversationId}/messages`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.messages = data.messages || [];
                this.renderMessages();
            } else {
                throw new Error(data.message || 'Failed to load messages');
            }

        } catch (error) {
            console.error('[DirectChat] Error loading messages:', error);
            this.showError('Failed to load messages: ' + error.message);
        }
    }

    renderMessages() {
        const container = document.getElementById('messages-container');
        if (!container) return;

        // Hide welcome message if we have messages
        const welcomeMessage = document.getElementById('welcome-message');
        if (this.messages.length > 0 && welcomeMessage) {
            welcomeMessage.style.display = 'none';
        }

        // Clear existing messages except welcome
        const existingMessages = container.querySelectorAll('.message-item');
        existingMessages.forEach(msg => msg.remove());

        if (this.messages.length === 0) {
            if (welcomeMessage) {
                welcomeMessage.style.display = 'block';
            }
            return;
        }

        this.messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            container.appendChild(messageElement);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    createMessageElement(message) {
        const isOwn = message.sender_id === this.currentUserId;
        const senderName = message.sender ? message.sender.name : (isOwn ? this.currentUserName : this.otherUserName);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-item flex ${isOwn ? 'justify-end' : 'justify-start'} mb-4`;
        
        messageDiv.innerHTML = `
            <div class="max-w-xs lg:max-w-md">
                ${!isOwn ? `<p class="text-xs text-gray-600 mb-1 ml-1">${senderName}</p>` : ''}
                <div class="px-4 py-3 rounded-2xl ${isOwn ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white' : 'bg-gray-100 text-gray-900'}">
                    <p class="text-sm leading-relaxed">${this.escapeHtml(message.message_text)}</p>
                </div>
                <p class="text-xs text-gray-500 mt-1 ${isOwn ? 'text-right' : 'text-left'} ml-1">
                    ${this.formatTime(message.created_at)}
                </p>
            </div>
        `;
        
        return messageDiv;
    }

    handleNewMessage(data) {
        // Add message to current messages
        this.messages.push({
            message_id: data.message_id,
            sender_id: data.sender_id,
            message_text: data.message_text,
            created_at: data.created_at,
            sender: { name: data.sender_name }
        });

        this.renderMessages();
        
        // Show notification for received messages
        if (data.sender_id !== this.currentUserId) {
            this.showNotification(`New message from ${data.sender_name}`);
        }
    }

    handleTypingIndicator(data) {
        const indicator = document.getElementById('typing-indicator');
        if (!indicator) return;

        if (data.is_typing) {
            indicator.classList.remove('hidden');
            // Auto-hide after 5 seconds
            setTimeout(() => {
                indicator.classList.add('hidden');
            }, 5000);
        } else {
            indicator.classList.add('hidden');
        }
    }

    setupEventListeners() {
        // Send message
        const sendButton = document.getElementById('send-button');
        const messageInput = document.getElementById('message-input');

        if (sendButton) {
            sendButton.addEventListener('click', () => this.sendMessage());
        }

        if (messageInput) {
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            messageInput.addEventListener('input', (e) => {
                this.updateCharCount();
                this.handleTyping();
            });
        }

        // Refresh button
        const refreshButton = document.getElementById('refresh-chat');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.loadMessages();
            });
        }
    }

    async sendMessage() {
        const messageInput = document.getElementById('message-input');
        const text = messageInput.value.trim();

        if (!text) return;

        const sendButton = document.getElementById('send-button');
        sendButton.disabled = true;

        try {
            const response = await fetch('/api/chat/messages', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: this.conversationId,
                    message_text: text
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                this.updateCharCount();
                
                // Add message immediately to UI
                this.handleNewMessage({
                    message_id: data.message.message_id,
                    sender_id: data.message.sender_id,
                    message_text: data.message.message_text,
                    created_at: data.message.created_at,
                    sender_name: this.currentUserName
                });
            } else {
                throw new Error(data.message || 'Failed to send message');
            }

        } catch (error) {
            console.error('[DirectChat] Error sending message:', error);
            this.showError('Failed to send message: ' + error.message);
        } finally {
            sendButton.disabled = false;
            messageInput.focus();
        }
    }

    handleTyping() {
        // Clear existing timeout
        clearTimeout(this.typingTimeout);

        // Send typing indicator
        fetch('/api/chat/typing', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: this.conversationId,
                is_typing: true
            })
        }).catch(console.error);

        // Stop typing after 3 seconds
        this.typingTimeout = setTimeout(() => {
            fetch('/api/chat/typing', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: this.conversationId,
                    is_typing: false
                })
            }).catch(console.error);
        }, 3000);
    }

    async updateOnlineStatus() {
        try {
            await fetch('/api/chat/online-status', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: 'online' })
            });
        } catch (error) {
            console.error('[DirectChat] Error updating online status:', error);
        }
    }

    updateCharCount() {
        const messageInput = document.getElementById('message-input');
        const charCount = document.getElementById('char-count');
        if (messageInput && charCount) {
            charCount.textContent = messageInput.value.length;
        }
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000);
    }

    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.directChat = new DirectChat();
});
</script>
@endpush
