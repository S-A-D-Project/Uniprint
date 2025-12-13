@extends('layouts.app')

@section('title', 'Direct Chat - Customer & Business')
@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        <div class="col-12">
            <!-- Chat Header -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-chat-dots-fill text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">UniPrint Direct Chat</h5>
                                    <small class="text-white-50">Customer ↔ Business User</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span id="connection-status" class="badge bg-warning">
                                    <i class="bi bi-wifi me-1"></i>Connecting...
                                </span>
                                <button id="refresh-chat" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Interface -->
            <div class="row">
                <div class="col-12">

                    <!-- Chat Messages Card -->
                    <div class="card border-0 shadow-sm mb-3" style="height: 500px;">
                        <!-- Chat Status Bar -->
                        <div class="card-header bg-light border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                        <small class="text-muted">
                                            <i class="bi bi-shield-check me-1"></i>Secure messaging
                                        </small>
                                        <span id="typing-indicator" class="text-primary fst-italic ms-3 d-none">
                                            <i class="bi bi-three-dots"></i> typing...
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i>
                                        Active now
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Messages Container -->
                        <div class="card-body p-0 position-relative" style="height: calc(100% - 60px);">
                            <div id="messages-container" class="h-100 overflow-auto p-3">
                                <div id="welcome-message" class="text-center py-5">
                                    <div class="mb-4">
                                        <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bi bi-chat-dots-fill text-white fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-2">Welcome to Direct Chat</h4>
                                    <p class="text-muted mb-3">Real-time messaging between Customer and Business User</p>
                                    <div class="d-inline-flex align-items-center px-3 py-2 bg-success bg-opacity-10 text-success rounded-pill">
                                        <i class="bi bi-lightning-charge-fill me-2"></i>
                                        <small class="fw-medium">Powered by Pusher CDN v7.0</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-bottom">
                            <!-- Quick Actions -->
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                                    <i class="bi bi-paperclip me-1"></i>
                                    <span class="d-none d-sm-inline">Attach</span>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                                    <i class="bi bi-image me-1"></i>
                                    <span class="d-none d-sm-inline">Image</span>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                                    <i class="bi bi-emoji-smile me-1"></i>
                                    <span class="d-none d-sm-inline">Emoji</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Input Area -->
                            <div class="row g-3 align-items-end">
                                <div class="col">
                                    <div class="form-floating">
                                        <textarea 
                                            id="message-input" 
                                            class="form-control" 
                                            placeholder="Type your message here..."
                                            style="height: 80px; resize: none;"
                                            maxlength="1000"></textarea>
                                        <label for="message-input">Type your message here...</label>
                                    </div>
                                    
                                    <!-- Input Info -->
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="d-flex gap-3">
                                            <small class="text-muted">
                                                <kbd class="bg-light text-dark border">Enter</kbd> to send
                                            </small>
                                            <small class="text-muted d-none d-sm-inline">
                                                <kbd class="bg-light text-dark border">Shift</kbd> + 
                                                <kbd class="bg-light text-dark border">Enter</kbd> for new line
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <span id="char-count">0</span>/1000
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Send Button -->
                                <div class="col-auto">
                                    <button 
                                        id="send-button" 
                                        class="btn btn-primary btn-lg d-flex align-items-center"
                                        disabled>
                                        <i class="bi bi-send-fill me-2"></i>
                                        <span class="d-none d-sm-inline">Send</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Bootstrap-compatible animations */
    .message-item {
        animation: fadeInUp 0.3s ease-out;
    }
    
    @keyframes fadeInUp {
        from { 
            opacity: 0; 
            transform: translateY(15px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
    
    /* Enhanced scrollbar for messages */
    #messages-container::-webkit-scrollbar {
        width: 8px;
    }
    
    #messages-container::-webkit-scrollbar-track {
        background: var(--bs-light);
        border-radius: 4px;
    }
    
    #messages-container::-webkit-scrollbar-thumb {
        background: var(--bs-primary);
        border-radius: 4px;
        border: 2px solid var(--bs-light);
    }
    
    #messages-container::-webkit-scrollbar-thumb:hover {
        background: var(--bs-primary-text-emphasis);
    }
    
    /* Message bubble enhancements */
    .message-bubble {
        max-width: 75%;
        word-wrap: break-word;
    }
    
    .message-bubble.own {
        background: linear-gradient(135deg, var(--bs-primary), var(--bs-info)) !important;
    }
    
    .message-bubble.other {
        background-color: var(--bs-light);
        border: 1px solid var(--bs-border-color);
    }
    
    /* Typing indicator animation */
    .typing-dots {
        display: inline-block;
    }
    
    .typing-dots::after {
        content: '';
        animation: typing 1.4s infinite;
    }
    
    @keyframes typing {
        0%, 60%, 100% { content: ''; }
        30% { content: '.'; }
        60% { content: '..'; }
        90% { content: '...'; }
    }
    
    /* Connection status transitions */
    .connection-badge {
        transition: all 0.3s ease;
    }
    
    /* Enhanced card hover effects */
    .chat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush

@push('scripts')
<!-- Pusher CDN v7.0 -->
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
class DirectChatSimple {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.conversationId = '{{ $conversationId }}';
        this.currentUserId = '{{ $currentUserData["user_id"] }}';
        this.currentUserName = '{{ $currentUserData["name"] }}';
        this.messages = [];
        this.typingTimeout = null;
        this.retryAttempts = 0;
        this.maxRetries = 3;
        
        this.init();
    }

    init() {
        console.log('[DirectChatSimple] Initializing simple direct chat...');
        console.log('[DirectChatSimple] Conversation ID:', this.conversationId);
        console.log('[DirectChatSimple] Current User:', this.currentUserName);
        
        this.setupPusher();
        this.setupEventListeners();
        this.updateOnlineStatus();
        
        // Update online status every 30 seconds
        setInterval(() => this.updateOnlineStatus(), 30000);
    }

    setupPusher() {
        try {
            const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
            const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
            
            console.log('[DirectChatSimple] Pusher Config:', { key: pusherKey, cluster: pusherCluster });
            
            if (!pusherKey || pusherKey === 'your_app_key') {
                console.error('[Pusher] Invalid configuration');
                this.updateConnectionStatus('error', 'Invalid Pusher configuration');
                return;
            }
            
            this.pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                enabledTransports: ['ws', 'wss'],
                forceTLS: true
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

        // Bootstrap-based status display
        statusElement.className = 'badge connection-badge';
        
        switch (status) {
            case 'connected':
                statusElement.className += ' bg-success';
                statusElement.innerHTML = `
                    <i class="bi bi-wifi me-1"></i>${message}
                `;
                break;
            case 'disconnected':
                statusElement.className += ' bg-warning';
                statusElement.innerHTML = `
                    <i class="bi bi-wifi-off me-1"></i>${message}
                `;
                break;
            case 'error':
                statusElement.className += ' bg-danger';
                statusElement.innerHTML = `
                    <i class="bi bi-exclamation-triangle me-1"></i>${message}
                `;
                break;
            default:
                statusElement.className += ' bg-warning';
                statusElement.innerHTML = `
                    <i class="bi bi-wifi me-1"></i>${message}
                `;
        }
    }

    subscribeToConversation() {
        if (!this.pusher) {
            console.warn('[Pusher] No pusher instance available, using fallback');
            return;
        }

        const channelName = `direct-chat.${this.conversationId}`;
        
        try {
            console.log('[Pusher] Subscribing to channel:', channelName);
            this.channel = this.pusher.subscribe(channelName);

            this.channel.bind('new-message', (data) => {
                console.log('[Pusher] New message received:', data);
                this.handleNewMessage(data);
            });

            this.channel.bind('user-typing', (data) => {
                console.log('[Pusher] Typing indicator:', data);
                this.handleTypingIndicator(data);
            });

            this.channel.bind('pusher:subscription_succeeded', () => {
                console.log('[Pusher] Successfully subscribed to channel:', channelName);
                this.updateConnectionStatus('connected', 'Connected');
            });

            this.channel.bind('pusher:subscription_error', (error) => {
                console.error('[Pusher] Subscription error:', error);
                this.updateConnectionStatus('error', 'Subscription Failed');
            });

        } catch (error) {
            console.error('[Pusher] Channel subscription error:', error);
            this.updateConnectionStatus('error', 'Channel Error');
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
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-item mb-3 d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}`;
        
        messageDiv.innerHTML = `
            <div class="message-bubble ${isOwn ? 'own' : 'other'}">
                ${!isOwn ? `
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 24px; height: 24px; font-size: 0.75rem;">
                            ${message.sender_name.charAt(0).toUpperCase()}
                        </div>
                        <small class="text-muted fw-medium">${this.escapeHtml(message.sender_name)}</small>
                    </div>
                ` : ''}
                
                <div class="px-3 py-2 rounded-3 ${isOwn ? 'text-white' : 'text-dark'}">
                    <p class="mb-0 small">${this.escapeHtml(message.message_text)}</p>
                </div>
                
                <div class="mt-1 ${isOwn ? 'text-end' : 'text-start'}">
                    <small class="text-muted">${this.formatTime(message.timestamp)}</small>
                    ${isOwn ? `
                        <i class="bi bi-check2-all text-primary ms-1" style="font-size: 0.75rem;"></i>
                    ` : ''}
                </div>
            </div>
        `;
        
        return messageDiv;
    }

    handleNewMessage(data) {
        // Check if message already exists to prevent duplicates
        const existingMessage = this.messages.find(msg => msg.message_id === data.message_id);
        if (existingMessage) {
            return;
        }

        const messageData = {
            message_id: data.message_id || Date.now(),
            sender_id: data.sender_id,
            sender_name: data.sender_name,
            message_text: data.message_text,
            timestamp: data.timestamp || new Date().toISOString()
        };

        // Only add to UI if it's from another user (sender already added their own message)
        if (data.sender_id !== this.currentUserId) {
            this.addMessageToUI(messageData);
            this.showNotification(`New message from ${data.sender_name}`);
        }
    }

    handleTypingIndicator(data) {
        const indicator = document.getElementById('typing-indicator');
        if (!indicator) return;

        if (data.is_typing) {
            indicator.classList.remove('d-none');
            indicator.innerHTML = `<i class="bi bi-three-dots typing-dots"></i> ${data.user_name} is typing...`;
            // Auto-hide after 5 seconds
            setTimeout(() => {
                indicator.classList.add('d-none');
            }, 5000);
        } else {
            indicator.classList.add('d-none');
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

        // Refresh chat button
        const refreshButton = document.getElementById('refresh-chat');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.clearChat();
                this.showNotification('Chat cleared');
            });
        }
    }

    async sendMessage() {
        const messageInput = document.getElementById('message-input');
        const text = messageInput.value.trim();

        if (!text || text.length > 1000) return;

        const sendButton = document.getElementById('send-button');
        const originalText = sendButton.innerHTML;
        
        // Update button state
        sendButton.disabled = true;
        sendButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i><span class="d-none d-sm-inline">Sending...</span>';

        try {
            const messageData = {
                message_id: Date.now().toString(),
                sender_id: this.currentUserId,
                sender_name: this.currentUserName,
                message_text: text,
                timestamp: new Date().toISOString(),
                conversation_id: this.conversationId
            };

            // Send to server for Pusher broadcasting
            const response = await fetch('/api/chat/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(messageData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success) {
                // Add message immediately to UI for sender
                this.addMessageToUI(messageData);
                
                // Clear input
                messageInput.value = '';
                this.updateCharCount();
                
                console.log('[DirectChat] Message sent successfully');
            } else {
                throw new Error(result.message || 'Failed to send message');
            }

        } catch (error) {
            console.error('[DirectChatSimple] Error sending message:', error);
            this.showError('Failed to send message. Please try again.');
            
            // Fallback to local simulation if server fails
            const messageData = {
                message_id: Date.now().toString(),
                sender_id: this.currentUserId,
                sender_name: this.currentUserName,
                message_text: text,
                timestamp: new Date().toISOString()
            };
            
            this.addMessageToUI(messageData);
            this.simulatePusherBroadcast('new-message', messageData);
            
            messageInput.value = '';
            this.updateCharCount();
        } finally {
            // Reset button state
            sendButton.disabled = false;
            sendButton.innerHTML = originalText;
            messageInput.focus();
        }
    }
    
    addMessageToUI(messageData) {
        // Check for duplicates
        const exists = this.messages.find(msg => msg.message_id === messageData.message_id);
        if (exists) return;
        
        // Add to messages array
        this.messages.push(messageData);
        
        // Save to local storage
        if (typeof saveMessageToStorage === 'function') {
            saveMessageToStorage(messageData);
        }
        
        // Hide welcome message if visible
        const welcomeMessage = document.getElementById('welcome-message');
        if (welcomeMessage && !welcomeMessage.classList.contains('d-none')) {
            welcomeMessage.classList.add('d-none');
        }
        
        // Create and add message element
        const messageElement = this.createMessageElement(messageData);
        const container = document.getElementById('messages-container');
        container.appendChild(messageElement);
        
        // Scroll to bottom smoothly
        container.scrollTop = container.scrollHeight;
    }

    simulatePusherBroadcast(eventName, data) {
        // Simulate cross-tab communication for demo
        const event = new CustomEvent('pusher-simulation', {
            detail: { 
                eventName, 
                data, 
                channel: `direct-chat.${this.conversationId}` 
            }
        });
        window.dispatchEvent(event);
    }

    handleTyping() {
        // Clear existing timeout
        clearTimeout(this.typingTimeout);

        // Broadcast typing indicator
        if (this.channel) {
            this.simulatePusherBroadcast('user-typing', {
                user_id: this.currentUserId,
                user_name: this.currentUserName,
                is_typing: true
            });
        }

        // Stop typing after 3 seconds
        this.typingTimeout = setTimeout(() => {
            if (this.channel) {
                this.simulatePusherBroadcast('user-typing', {
                    user_id: this.currentUserId,
                    user_name: this.currentUserName,
                    is_typing: false
                });
            }
        }, 3000);
    }

    updateOnlineStatus() {
        // Just update the UI - no server calls needed
        const indicator = document.getElementById('online-indicator');
        if (indicator) {
            indicator.className = 'w-3 h-3 rounded-full bg-green-400';
        }
    }

    clearChat() {
        // Clear messages array
        this.messages = [];
        
        // Clear local storage
        try {
            localStorage.removeItem('directChatMessages');
        } catch (error) {
            console.error('Error clearing storage:', error);
        }
        
        // Clear UI
        const container = document.getElementById('messages-container');
        if (container) {
            // Remove all message elements
            const messageElements = container.querySelectorAll('.message-item');
            messageElements.forEach(element => element.remove());
            
            // Show welcome message
            const welcomeMessage = document.getElementById('welcome-message');
            if (welcomeMessage) {
                welcomeMessage.classList.remove('d-none');
            }
        }
    }

    updateCharCount() {
        const messageInput = document.getElementById('message-input');
        const charCount = document.getElementById('char-count');
        const sendButton = document.getElementById('send-button');
        
        if (messageInput && charCount) {
            const length = messageInput.value.length;
            charCount.textContent = length;
            
            // Enable/disable send button based on input
            if (sendButton) {
                if (length > 0 && length <= 1000) {
                    sendButton.disabled = false;
                    sendButton.classList.remove('disabled');
                } else {
                    sendButton.disabled = true;
                    sendButton.classList.add('disabled');
                }
            }
            
            // Change color based on character limit
            if (length > 900) {
                charCount.classList.remove('text-muted');
                charCount.classList.add('text-warning');
            } else if (length > 950) {
                charCount.classList.remove('text-warning');
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-warning', 'text-danger');
                charCount.classList.add('text-muted');
            }
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
        this.showToast(message, 'danger');
    }

    showNotification(message) {
        this.showToast(message, 'success');
    }
    
    showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const iconClass = type === 'danger' ? 'bi-exclamation-triangle-fill' : 
                         type === 'success' ? 'bi-check-circle-fill' : 
                         'bi-info-circle-fill';
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${iconClass} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Initialize Bootstrap toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: type === 'danger' ? 5000 : 3000
        });
        
        bsToast.show();
        
        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
}

// Enhanced cross-tab communication
window.addEventListener('pusher-simulation', (event) => {
    const { eventName, data, channel } = event.detail;
    if (channel === `direct-chat.sarah-business-direct-chat` && window.directChatSimple) {
        console.log('[CrossTab] Received event:', eventName, data);
        
        if (eventName === 'new-message') {
            window.directChatSimple.handleNewMessage(data);
        } else if (eventName === 'user-typing') {
            window.directChatSimple.handleTypingIndicator(data);
        }
    }
});

// Local storage for message persistence across tabs
function saveMessageToStorage(message) {
    try {
        const messages = JSON.parse(localStorage.getItem('directChatMessages') || '[]');
        messages.push(message);
        // Keep only last 50 messages
        if (messages.length > 50) {
            messages.splice(0, messages.length - 50);
        }
        localStorage.setItem('directChatMessages', JSON.stringify(messages));
    } catch (error) {
        console.error('Error saving message to storage:', error);
    }
}

function loadMessagesFromStorage() {
    try {
        return JSON.parse(localStorage.getItem('directChatMessages') || '[]');
    } catch (error) {
        console.error('Error loading messages from storage:', error);
        return [];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chat
    window.directChatSimple = new DirectChatSimple();
    
    // Load existing messages from storage after a short delay
    setTimeout(() => {
        const storedMessages = loadMessagesFromStorage();
        if (storedMessages.length > 0) {
            window.directChatSimple.messages = [];
            
            // Hide welcome message first
            const welcomeMessage = document.getElementById('welcome-message');
            if (welcomeMessage) {
                welcomeMessage.classList.add('d-none');
            }
            
            // Add each message to UI
            storedMessages.forEach(message => {
                const messageElement = window.directChatSimple.createMessageElement(message);
                const container = document.getElementById('messages-container');
                if (container) {
                    container.appendChild(messageElement);
                }
                window.directChatSimple.messages.push(message);
            });
            
            // Scroll to bottom
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        console.log('[DirectChat] Loaded', storedMessages.length, 'messages from storage');
    }, 100);
    
    // Add test message functionality (for demonstration)
    window.addTestMessage = function() {
        if (window.directChatSimple) {
            const testMessage = {
                message_id: 'test-' + Date.now(),
                sender_id: 'other-user',
                sender_name: 'Test User',
                message_text: 'This is a test message to verify chat functionality!',
                timestamp: new Date().toISOString()
            };
            window.directChatSimple.handleNewMessage(testMessage);
        }
    };
    
    // Test Pusher connection
    window.testPusher = function() {
        if (window.directChatSimple && window.directChatSimple.pusher) {
            console.log('Pusher State:', window.directChatSimple.pusher.connection.state);
            console.log('Channel:', window.directChatSimple.channel);
            return {
                connected: window.directChatSimple.pusher.connection.state === 'connected',
                channel: window.directChatSimple.channel ? 'subscribed' : 'not subscribed'
            };
        }
        return { error: 'Pusher not initialized' };
    };
    
    console.log('[DirectChat] Initialization complete.');
    console.log('Available test functions:');
    console.log('- addTestMessage() - Add a test message');
    console.log('- testPusher() - Check Pusher connection status');
});
</script>
@endpush
