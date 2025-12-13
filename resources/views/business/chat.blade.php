@extends('layouts.business')

@section('title', 'Business Chat')
@section('page-title', 'Customer Communications')
@section('page-subtitle', 'Real-time chat with customers')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-200px)]">
    <!-- Chat Sidebar -->
    <div class="lg:col-span-1 bg-card border border-border rounded-xl shadow-card overflow-hidden">
        <div class="p-4 border-b border-border bg-primary/5">
            <h3 class="font-semibold text-lg flex items-center gap-2">
                <i data-lucide="message-circle" class="h-5 w-5 text-primary"></i>
                Customer Chats
            </h3>
            <div id="connection-status" class="mt-2 text-xs px-2 py-1 rounded-md bg-yellow-100 text-yellow-800">
                Connecting...
            </div>
        </div>
        
        <div class="p-3">
            <input type="text" id="search-conversations" 
                   placeholder="Search conversations..." 
                   class="w-full px-3 py-2 border border-input rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-ring">
        </div>
        
        <div id="conversations-list" class="overflow-y-auto h-[calc(100%-120px)]">
            <!-- Conversations will be loaded here -->
        </div>
    </div>

    <!-- Chat Area -->
    <div class="lg:col-span-3 bg-card border border-border rounded-xl shadow-card overflow-hidden flex flex-col">
        <!-- Chat Header -->
        <div id="chat-header" class="p-4 border-b border-border bg-primary/5 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                        <span id="customer-avatar"></span>
                    </div>
                    <div>
                        <h4 id="customer-name" class="font-semibold"></h4>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <div id="online-indicator" class="w-2 h-2 rounded-full bg-gray-400"></div>
                            <span id="customer-status">Offline</span>
                            <span id="typing-indicator" class="text-primary italic hidden">typing...</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="refresh-chat" class="p-2 hover:bg-secondary rounded-md transition-colors">
                        <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="message-circle" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Select a conversation</h3>
                <p class="text-muted-foreground">Choose a customer to start chatting</p>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 hidden">
            <!-- Messages will be loaded here -->
        </div>

        <!-- Message Input -->
        <div id="message-input-area" class="p-4 border-t border-border hidden">
            <div class="flex gap-3">
                <textarea id="message-input" 
                          placeholder="Type your message..." 
                          class="flex-1 px-4 py-2 border border-input rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-ring"
                          rows="1"></textarea>
                <button id="send-button" 
                        class="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="send" class="h-4 w-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Pusher CDN -->
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
class BusinessChat {
    constructor() {
        this.pusher = null;
        this.currentConversation = null;
        this.conversations = [];
        this.messages = [];
        this.typingTimeout = null;
        this.currentUserId = '{{ auth()->id() }}';
        this.currentUserName = '{{ auth()->user()->name }}';
        this.subscribedChannels = new Map();
        this.retryAttempts = 0;
        this.maxRetries = 3;
        
        this.init();
    }

    init() {
        console.log('[BusinessChat] Initializing...');
        this.setupPusher();
        this.setupEventListeners();
        this.loadConversations();
        this.updateOnlineStatus();
        
        // Update online status every 30 seconds
        setInterval(() => this.updateOnlineStatus(), 30000);
        
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    setupPusher() {
        try {
            // Use backend Pusher configuration
            const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
            const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
            
            console.log('[BusinessChat] Pusher Config:', { key: pusherKey, cluster: pusherCluster });
            
            if (!pusherKey || pusherKey === 'your_app_key') {
                console.error('[Pusher] Invalid configuration. Please set PUSHER_APP_KEY in .env');
                this.updateConnectionStatus('error');
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
                this.updateConnectionStatus('connected');
                this.retryAttempts = 0;
            });

            this.pusher.connection.bind('disconnected', () => {
                console.log('[Pusher] Disconnected');
                this.updateConnectionStatus('disconnected');
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('[Pusher] Connection error:', error);
                this.updateConnectionStatus('error');
                this.handleConnectionError();
            });

        } catch (error) {
            console.error('[Pusher] Setup error:', error);
            this.updateConnectionStatus('error');
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

    updateConnectionStatus(status) {
        const statusElement = document.getElementById('connection-status');
        if (!statusElement) return;

        statusElement.className = `mt-2 text-xs px-2 py-1 rounded-md`;
        
        switch (status) {
            case 'connected':
                statusElement.className += ' bg-green-100 text-green-800';
                statusElement.textContent = 'Connected';
                break;
            case 'disconnected':
                statusElement.className += ' bg-yellow-100 text-yellow-800';
                statusElement.textContent = 'Disconnected';
                break;
            case 'error':
                statusElement.className += ' bg-red-100 text-red-800';
                statusElement.textContent = 'Connection Error';
                break;
            default:
                statusElement.className += ' bg-yellow-100 text-yellow-800';
                statusElement.textContent = 'Connecting...';
        }
    }

    async loadConversations() {
        try {
            console.log('[BusinessChat] Loading conversations...');
            
            const response = await fetch('/api/chat/conversations', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('[BusinessChat] Conversations loaded:', data);

            if (data.success) {
                this.conversations = data.conversations || [];
                this.renderConversations();
            } else {
                throw new Error(data.message || 'Failed to load conversations');
            }

        } catch (error) {
            console.error('[BusinessChat] Error loading conversations:', error);
            this.showError('Failed to load conversations: ' + error.message);
        }
    }

    renderConversations() {
        const container = document.getElementById('conversations-list');
        if (!container) return;

        if (this.conversations.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-muted-foreground">
                    <div class="w-12 h-12 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="message-circle" class="h-6 w-6"></i>
                    </div>
                    <p class="text-sm">No conversations yet</p>
                    <p class="text-xs">Customers will appear here when they start chatting</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }

        container.innerHTML = this.conversations.map(conversation => {
            const participant = conversation.participant;
            const lastMessage = conversation.last_message;
            const unreadCount = conversation.unread_count || 0;
            
            return `
                <div class="conversation-item p-3 border-b border-border cursor-pointer hover:bg-secondary/30 transition-colors" 
                     data-conversation-id="${conversation.conversation_id}">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                            ${participant.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-medium text-sm truncate">${participant.name}</h4>
                                ${unreadCount > 0 ? `<span class="bg-primary text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">${unreadCount}</span>` : ''}
                            </div>
                            <p class="text-xs text-muted-foreground truncate">
                                ${lastMessage ? lastMessage.message_text : 'No messages yet'}
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                ${lastMessage ? this.formatTime(lastMessage.created_at) : ''}
                            </p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Add click handlers
        container.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => {
                const conversationId = item.dataset.conversationId;
                this.openConversation(conversationId);
            });
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    async openConversation(conversationId) {
        try {
            console.log('[BusinessChat] Opening conversation:', conversationId);
            
            const conversation = this.conversations.find(c => c.conversation_id === conversationId);
            if (!conversation) {
                throw new Error('Conversation not found');
            }

            this.currentConversation = conversation;
            
            // Update UI
            this.showChatInterface();
            this.updateChatHeader(conversation);
            
            // Load messages
            await this.loadMessages(conversationId);
            
            // Subscribe to real-time updates
            this.subscribeToConversation(conversationId);
            
        } catch (error) {
            console.error('[BusinessChat] Error opening conversation:', error);
            this.showError('Failed to open conversation: ' + error.message);
        }
    }

    showChatInterface() {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('chat-header').classList.remove('hidden');
        document.getElementById('messages-container').classList.remove('hidden');
        document.getElementById('message-input-area').classList.remove('hidden');
    }

    updateChatHeader(conversation) {
        const participant = conversation.participant;
        
        document.getElementById('customer-avatar').textContent = participant.name.charAt(0).toUpperCase();
        document.getElementById('customer-name').textContent = participant.name;
        document.getElementById('customer-status').textContent = 'Online'; // TODO: Implement real status
        
        // Update online indicator
        const indicator = document.getElementById('online-indicator');
        indicator.className = 'w-2 h-2 rounded-full bg-green-400'; // TODO: Dynamic status
    }

    async loadMessages(conversationId) {
        try {
            const response = await fetch(`/api/chat/conversations/${conversationId}/messages`, {
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
            console.error('[BusinessChat] Error loading messages:', error);
            this.showError('Failed to load messages: ' + error.message);
        }
    }

    renderMessages() {
        const container = document.getElementById('messages-container');
        if (!container) return;

        if (this.messages.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted-foreground py-8">
                    <i data-lucide="message-circle" class="h-12 w-12 mx-auto mb-2"></i>
                    <p>No messages yet</p>
                    <p class="text-sm">Start the conversation!</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }

        container.innerHTML = this.messages.map(message => {
            const isOwn = message.sender_id === this.currentUserId;
            const senderName = message.sender ? message.sender.name : 'Unknown';
            
            return `
                <div class="flex ${isOwn ? 'justify-end' : 'justify-start'} mb-4">
                    <div class="max-w-xs lg:max-w-md">
                        ${!isOwn ? `<p class="text-xs text-muted-foreground mb-1">${senderName}</p>` : ''}
                        <div class="px-4 py-2 rounded-lg ${isOwn ? 'bg-primary text-white' : 'bg-secondary text-secondary-foreground'}">
                            <p class="text-sm">${message.message_text}</p>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1 ${isOwn ? 'text-right' : 'text-left'}">
                            ${this.formatTime(message.created_at)}
                        </p>
                    </div>
                </div>
            `;
        }).join('');

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    subscribeToConversation(conversationId) {
        if (!this.pusher) return;

        const channelName = `conversation.${conversationId}`;
        
        // Unsubscribe from previous channel
        this.subscribedChannels.forEach((channel, name) => {
            this.pusher.unsubscribe(name);
        });
        this.subscribedChannels.clear();

        try {
            const channel = this.pusher.subscribe(channelName);
            this.subscribedChannels.set(channelName, channel);

            channel.bind('new-message', (data) => {
                console.log('[Pusher] New message received:', data);
                if (data.sender_id !== this.currentUserId) {
                    this.handleNewMessage(data);
                }
            });

            channel.bind('user-typing', (data) => {
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
        
        // Update conversation list
        this.loadConversations();
    }

    handleTypingIndicator(data) {
        const indicator = document.getElementById('typing-indicator');
        if (!indicator) return;

        if (data.is_typing) {
            indicator.classList.remove('hidden');
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

            messageInput.addEventListener('input', () => {
                this.handleTyping();
            });
        }

        // Refresh button
        const refreshButton = document.getElementById('refresh-chat');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.loadConversations();
                if (this.currentConversation) {
                    this.loadMessages(this.currentConversation.conversation_id);
                }
            });
        }
    }

    async sendMessage() {
        if (!this.currentConversation) return;

        const messageInput = document.getElementById('message-input');
        const text = messageInput.value.trim();

        if (!text) return;

        try {
            const response = await fetch('/api/chat/messages', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: this.currentConversation.conversation_id,
                    message_text: text
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                // Message will be added via Pusher event or we can add it directly
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
            console.error('[BusinessChat] Error sending message:', error);
            this.showError('Failed to send message: ' + error.message);
        }
    }

    handleTyping() {
        if (!this.currentConversation) return;

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
                conversation_id: this.currentConversation.conversation_id,
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
                    conversation_id: this.currentConversation.conversation_id,
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
            console.error('[BusinessChat] Error updating online status:', error);
        }
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    showError(message) {
        // Create error notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.businessChat = new BusinessChat();
});
</script>
@endpush
