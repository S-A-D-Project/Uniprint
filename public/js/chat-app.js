/**
 * UniPrint Real-time Chat Application
 * Using Pusher CDN for WebSocket communication
 * jQuery for DOM manipulation
 */

(function($) {
    'use strict';

    // Global Configuration
    const APP_CONFIG = {
        apiBaseUrl: '/api/chat',
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        currentUserId: window.Laravel?.user?.id || null,
        currentUserName: window.Laravel?.user?.name || 'User',
        currentUserRole: window.Laravel?.user?.role_type || 'customer',
        pusher: {
            key: window.Laravel?.pusher?.key || 'YOUR_PUSHER_KEY',
            cluster: window.Laravel?.pusher?.cluster || 'mt1',
            enabledTransports: ['ws', 'wss'],
            forceTLS: true,
            authEndpoint: '/api/chat/pusher/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        },
        performance: {
            messageThrottle: 100,     // ms between message sends
            typingThrottle: 1000,     // ms between typing indicators
            reconnectDelay: 2000,     // ms before reconnect attempt
            maxReconnectAttempts: 5,  // max reconnection attempts
            messageBufferSize: 100,   // max messages to buffer offline
            channelTimeout: 30000     // ms before channel subscription timeout
        },
        security: {
            maxMessageLength: 5000,
            allowedFileTypes: ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
            maxFileSize: 10 * 1024 * 1024 // 10MB
        }
    };

    class ChatApplication {
        constructor() {
            this.pusher = null;
            this.conversations = [];
            this.currentConversation = null;
            this.messages = [];
            this.typingTimeout = null;
            this.onlineStatusInterval = null;
            this.subscribedChannels = new Map();
            this.messageBuffer = [];
            this.reconnectAttempts = 0;
            this.isOnline = navigator.onLine;
            this.lastMessageTime = 0;
            this.lastTypingTime = 0;
            this.connectionState = 'disconnected';
            this.messageQueue = [];
            this.eventListeners = new Map();
            this.availableBusinesses = [];
            this.userRole = APP_CONFIG.currentUserRole || 'customer';
            
            this.init();
        }

        // ==================== INITIALIZATION ====================
        init() {
            console.log('[Chat] Initializing Enhanced Chat Application...');
            this.setupNetworkListeners();
            this.setupPusher();
            this.setupEventListeners();
            this.loadConversations();
            this.startOnlineStatusUpdates();
            this.startPerformanceMonitoring();
        }

        setupNetworkListeners() {
            window.addEventListener('online', () => {
                console.log('[Network] Back online');
                this.isOnline = true;
                this.handleReconnection();
            });

            window.addEventListener('offline', () => {
                console.log('[Network] Gone offline');
                this.isOnline = false;
                this.updateConnectionStatus('offline');
            });
        }

        // ==================== PUSHER SETUP ====================
        setupPusher() {
            try {
                // Initialize Pusher with enhanced configuration
                this.pusher = new Pusher(APP_CONFIG.pusher.key, {
                    cluster: APP_CONFIG.pusher.cluster,
                    enabledTransports: APP_CONFIG.pusher.enabledTransports,
                    forceTLS: APP_CONFIG.pusher.forceTLS,
                    authEndpoint: APP_CONFIG.pusher.authEndpoint,
                    auth: APP_CONFIG.pusher.auth,
                    activityTimeout: 30000,
                    pongTimeout: 6000
                });

                // Enhanced connection event handlers
                this.pusher.connection.bind('connected', () => {
                    console.log('[Pusher] Connected successfully');
                    this.connectionState = 'connected';
                    this.reconnectAttempts = 0;
                    this.updateConnectionStatus('connected');
                    this.processMessageQueue();
                    this.resubscribeToChannels();
                });

                this.pusher.connection.bind('connecting', () => {
                    console.log('[Pusher] Connecting...');
                    this.connectionState = 'connecting';
                    this.updateConnectionStatus('connecting');
                });

                this.pusher.connection.bind('disconnected', () => {
                    console.log('[Pusher] Disconnected');
                    this.connectionState = 'disconnected';
                    this.updateConnectionStatus('disconnected');
                    this.scheduleReconnection();
                });

                this.pusher.connection.bind('error', (err) => {
                    console.error('[Pusher] Connection error:', err);
                    this.connectionState = 'error';
                    this.updateConnectionStatus('error');
                    this.handleConnectionError(err);
                });

                this.pusher.connection.bind('state_change', (states) => {
                    console.log('[Pusher] State changed:', states.previous, '->', states.current);
                });

                console.log('[Pusher] Enhanced initialization complete');
            } catch (error) {
                console.error('[Pusher] Failed to initialize:', error);
                this.updateConnectionStatus('error');
                this.scheduleReconnection();
            }
        }

        updateConnectionStatus(status) {
            const $statusEl = $('#connectionStatus');
            $statusEl.removeClass('connected disconnected connecting offline error');
            
            switch(status) {
                case 'connected':
                    $statusEl.addClass('connected').html('<i class="bi bi-wifi"></i> Connected');
                    setTimeout(() => $statusEl.fadeOut(), 3000);
                    break;
                case 'disconnected':
                    $statusEl.addClass('disconnected').html('<i class="bi bi-wifi-off"></i> Disconnected');
                    $statusEl.show();
                    break;
                case 'connecting':
                    $statusEl.addClass('connecting').html('<i class="bi bi-wifi"></i> Connecting...');
                    $statusEl.show();
                    break;
                case 'offline':
                    $statusEl.addClass('offline').html('<i class="bi bi-wifi-off"></i> Offline');
                    $statusEl.show();
                    break;
                case 'error':
                    $statusEl.addClass('error').html('<i class="bi bi-exclamation-triangle"></i> Connection Error');
                    $statusEl.show();
                    break;
                default:
                    $statusEl.addClass('connecting').html('<i class="bi bi-wifi"></i> Connecting...');
                    $statusEl.show();
            }
        }

        handleConnectionError(error) {
            console.error('[Pusher] Connection error details:', error);
            
            // Handle specific error types
            if (error.error && error.error.data) {
                const errorData = error.error.data;
                if (errorData.code === 4001) {
                    console.error('[Pusher] Invalid app key');
                    this.showError('Invalid Pusher configuration. Please check your app key.');
                } else if (errorData.code === 4004) {
                    console.error('[Pusher] Over connection limit');
                    this.showError('Connection limit exceeded. Please try again later.');
                }
            }
        }

        scheduleReconnection() {
            if (this.reconnectAttempts >= APP_CONFIG.performance.maxReconnectAttempts) {
                console.log('[Pusher] Max reconnection attempts reached');
                this.showError('Unable to establish connection. Please refresh the page.');
                return;
            }

            const delay = APP_CONFIG.performance.reconnectDelay * Math.pow(2, this.reconnectAttempts);
            console.log(`[Pusher] Scheduling reconnection attempt ${this.reconnectAttempts + 1} in ${delay}ms`);
            
            setTimeout(() => {
                if (this.connectionState !== 'connected' && this.isOnline) {
                    this.reconnectAttempts++;
                    this.pusher.connect();
                }
            }, delay);
        }

        handleReconnection() {
            if (this.connectionState !== 'connected') {
                console.log('[Pusher] Attempting to reconnect...');
                this.reconnectAttempts = 0;
                this.pusher.connect();
            }
        }

        resubscribeToChannels() {
            console.log('[Pusher] Resubscribing to channels...');
            const channelIds = Array.from(this.subscribedChannels.keys());
            this.subscribedChannels.clear();
            
            channelIds.forEach(conversationId => {
                this.subscribeToConversation(conversationId);
            });
        }

        processMessageQueue() {
            if (this.messageQueue.length > 0) {
                console.log(`[Chat] Processing ${this.messageQueue.length} queued messages`);
                const queue = [...this.messageQueue];
                this.messageQueue = [];
                
                queue.forEach(messageData => {
                    this.sendMessage(messageData.text, messageData.conversationId);
                });
            }
        }

        subscribeToConversation(conversationId) {
            if (this.subscribedChannels.has(conversationId)) {
                return; // Already subscribed
            }

            // Use presence channel for active participant tracking
            const channelName = `presence-conversation.${conversationId}`;
            
            try {
                const channel = this.pusher.subscribe(channelName);

                // Enhanced message event handler with validation
                channel.bind('new-message', (data) => {
                    console.log('[Pusher] New message received:', data);
                    if (this.validateMessageData(data)) {
                        this.handleNewMessage(data);
                    } else {
                        console.warn('[Pusher] Invalid message data received:', data);
                    }
                });

                // Enhanced typing indicator with throttling
                channel.bind('user-typing', (data) => {
                    console.log('[Pusher] User typing event:', data);
                    if (this.validateTypingData(data) && data.user_id !== APP_CONFIG.currentUserId) {
                        this.showTypingIndicator(data);
                    }
                });

                // Enhanced read receipt handler
                channel.bind('message-read', (data) => {
                    console.log('[Pusher] Message read event:', data);
                    if (this.validateReadReceiptData(data)) {
                        this.updateMessageReadStatus(data);
                    }
                });

                // Presence events - member joined
                channel.bind('pusher:member_added', (member) => {
                    console.log('[Pusher] Member joined:', member);
                    this.updateOnlineIndicator(member.id, true);
                });

                // Presence events - member left  
                channel.bind('pusher:member_removed', (member) => {
                    console.log('[Pusher] Member left:', member);
                    this.updateOnlineIndicator(member.id, false);
                });

                // Channel subscription events
                channel.bind('pusher:subscription_succeeded', (members) => {
                    console.log(`[Pusher] Successfully subscribed to ${channelName}. Members: ${members.count}`);
                    this.updatePresenceList(members);
                });

                channel.bind('pusher:subscription_error', (error) => {
                    console.error(`[Pusher] Subscription error for ${channelName}:`, error);
                    this.handleSubscriptionError(conversationId, error);
                });

                this.subscribedChannels.set(conversationId, channel);
                console.log(`[Pusher] Subscribing to ${channelName}`);
                
            } catch (error) {
                console.error(`[Pusher] Failed to subscribe to ${channelName}:`, error);
                this.handleSubscriptionError(conversationId, error);
            }
        }

        unsubscribeFromConversation(conversationId) {
            const channel = this.subscribedChannels.get(conversationId);
            if (channel) {
                const channelName = `conversation.${conversationId}`;
                this.pusher.unsubscribe(channelName);
                this.subscribedChannels.delete(conversationId);
                console.log(`[Pusher] Unsubscribed from ${channelName}`);
            }
        }

        handleSubscriptionError(conversationId, error) {
            console.error(`[Pusher] Subscription error for conversation ${conversationId}:`, error);
            
            // Retry subscription after delay
            setTimeout(() => {
                if (this.currentConversation && this.currentConversation.conversation_id === conversationId) {
                    console.log(`[Pusher] Retrying subscription for ${conversationId}`);
                    this.subscribeToConversation(conversationId);
                }
            }, 5000);
        }

        // ==================== MESSAGE VALIDATION ====================
        validateMessageData(data) {
            return data && 
                   typeof data.message_id === 'string' &&
                   typeof data.sender_id === 'string' &&
                   typeof data.message_text === 'string' &&
                   data.message_text.length <= APP_CONFIG.security.maxMessageLength &&
                   typeof data.conversation_id === 'string';
        }

        validateTypingData(data) {
            return data &&
                   typeof data.user_id === 'string' &&
                   typeof data.is_typing === 'boolean';
        }

        validateReadReceiptData(data) {
            return data &&
                   Array.isArray(data.message_ids) &&
                   typeof data.reader_id === 'string';
        }

        // ==================== PERFORMANCE MONITORING ====================
        startPerformanceMonitoring() {
            // Monitor connection quality
            setInterval(() => {
                if (this.pusher && this.pusher.connection) {
                    const state = this.pusher.connection.state;
                    if (state !== 'connected' && this.isOnline) {
                        console.warn(`[Performance] Connection state: ${state}`);
                    }
                }
            }, 10000);

            // Monitor message buffer size
            setInterval(() => {
                if (this.messageBuffer.length > APP_CONFIG.performance.messageBufferSize * 0.8) {
                    console.warn(`[Performance] Message buffer approaching limit: ${this.messageBuffer.length}`);
                }
            }, 30000);
        }

        // ==================== EVENT LISTENERS ====================
        setupEventListeners() {
            const self = this;

            // Send message
            $('#sendButton').on('click', () => this.sendMessage());
            
            $('#messageInput').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Typing indicator
            $('#messageInput').on('input', () => this.handleTyping());

            // Search conversations
            $('#searchConversations').on('input', function() {
                self.searchConversations($(this).val());
            });

            // Refresh messages
            $('#refreshMessages').on('click', () => {
                if (this.currentConversation) {
                    this.loadMessages(this.currentConversation.conversation_id);
                }
            });

            // Back button (mobile)
            $('#backButton').on('click', () => this.showConversationsList());

            // Auto-resize textarea
            $('#messageInput').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // File attachment handling
            $('#attachmentButton').on('click', () => $('#fileInput').click());
            $('#fileInput').on('change', (e) => this.handleFileSelection(e));
            $('#removeFile').on('click', () => this.removeSelectedFile());

            // Mark messages as read when scrolling
            $('#chatMessages').on('scroll', () => this.markVisibleMessagesAsRead());

            // Request notification permission
            this.requestNotificationPermission();

            // Setup customer-specific features
            if (this.userRole === 'customer') {
                this.setupCustomerFeatures();
            }
        }

        // ==================== API CALLS ====================
        async apiCall(endpoint, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': APP_CONFIG.csrfToken,
                    'Accept': 'application/json'
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(`${APP_CONFIG.apiBaseUrl}${endpoint}`, options);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'API call failed');
                }
                
                return result;
            } catch (error) {
                console.error('[API] Error:', error);
                this.showError(error.message);
                throw error;
            }
        }

        // ==================== CUSTOMER FEATURES ====================
        setupCustomerFeatures() {
            // Start new chat button
            $('#startNewChatBtn').on('click', () => this.showBusinessList());
            
            // Load available businesses
            this.loadAvailableBusinesses();
        }

        async loadAvailableBusinesses() {
            try {
                const result = await this.apiCall('/available-businesses');
                this.availableBusinesses = result.businesses || [];
            } catch (error) {
                console.error('[Chat] Failed to load available businesses:', error);
            }
        }

        showBusinessList() {
            if (this.availableBusinesses.length === 0) {
                this.showError('No businesses available to chat with.');
                return;
            }

            const businessListHtml = this.availableBusinesses.map(business => {
                const initials = this.getInitials(business.name);
                return `
                    <div class="business-item" data-business-id="${business.user_id}">
                        <div class="d-flex align-items-center p-3 border rounded mb-2 cursor-pointer">
                            <div class="avatar me-3">
                                ${initials}
                            </div>
                            <div class="flex-1">
                                <div class="fw-bold">${this.escapeHtml(business.name)}</div>
                                <div class="text-muted small">${this.escapeHtml(business.email)}</div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </div>
                `;
            }).join('');

            $('#businessList').html(businessListHtml);

            // Add click handlers
            $('.business-item').on('click', (e) => {
                const businessId = $(e.currentTarget).data('business-id');
                this.startChatWithBusiness(businessId);
            });

            // Show modal
            $('#businessListModal').modal('show');
        }

        async startChatWithBusiness(businessId) {
            try {
                $('#businessListModal').modal('hide');
                
                const result = await this.apiCall('/conversations', 'POST', {
                    business_id: businessId
                });

                if (result.success) {
                    // Reload conversations to show the new one
                    await this.loadConversations();
                    
                    // Open the new conversation
                    this.openConversation(result.conversation.conversation_id);
                    
                    this.showSuccess('Chat started successfully!');
                } else {
                    this.showError(result.message || 'Failed to start chat');
                }
            } catch (error) {
                console.error('[Chat] Failed to start chat:', error);
                this.showError('Failed to start chat. Please try again.');
            }
        }

        // ==================== CONVERSATIONS ====================
        async loadConversations() {
            try {
                const result = await this.apiCall('/conversations');
                this.conversations = result.conversations || [];
                this.userRole = result.user_role || 'customer';
                this.renderConversations();
                
                // Update UI based on user role
                this.updateUIForUserRole();
            } catch (error) {
                $('#conversationsList').html(`
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load conversations</p>
                        <button class="btn btn-sm btn-primary mt-2" onclick="chatApp.loadConversations()">
                            Retry
                        </button>
                    </div>
                `);
            }
        }

        updateUIForUserRole() {
            if (this.userRole === 'business_user') {
                // Hide new chat button for business users
                $('#startNewChatBtn').hide();
                
                // Update empty state message
                $('#emptyState p').text('Wait for customers to start conversations with you');
            } else {
                // Show new chat button for customers
                $('#startNewChatBtn').show();
                
                // Update empty state message
                $('#emptyState p').text('Start a new conversation or select an existing one');
            }
        }

        renderConversations() {
            const $container = $('#conversationsList');
            
            if (this.conversations.length === 0) {
                const emptyMessage = this.userRole === 'customer' 
                    ? 'No conversations yet. Click "Start New Chat" to begin.'
                    : 'No conversations yet. Wait for customers to contact you.';
                    
                $container.html(`
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">${emptyMessage}</p>
                    </div>
                `);
                return;
            }

            const html = this.conversations.map(conv => {
                const participant = conv.participant;
                const initials = this.getInitials(participant.name);
                const lastMessage = conv.last_message;
                const unreadCount = conv.unread_count || 0;
                const timeAgo = this.formatTimeAgo(conv.last_message_at);
                const preview = lastMessage 
                    ? this.escapeHtml(lastMessage.message_text.substring(0, 40)) + '...' 
                    : 'No messages yet';

                const roleIndicator = this.userRole === 'customer' 
                    ? `<small class="text-muted">${participant.role === 'business_user' ? 'Business' : 'Customer'}</small>`
                    : `<small class="text-muted">${participant.role === 'customer' ? 'Customer' : 'Business'}</small>`;

                return `
                    <div class="conversation-item" data-conversation-id="${conv.conversation_id}">
                        <div class="d-flex align-items-start">
                            <div class="avatar">
                                ${initials}
                                <div class="online-indicator" data-user-id="${participant.user_id}" style="display: none;"></div>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    ${this.escapeHtml(participant.name)}
                                    ${roleIndicator}
                                </div>
                                <div class="conversation-preview">${preview}</div>
                            </div>
                            <div class="conversation-meta">
                                <div class="conversation-time">${timeAgo}</div>
                                ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $container.html(html);

            // Add click handlers
            $('.conversation-item').on('click', (e) => {
                const conversationId = $(e.currentTarget).data('conversation-id');
                this.openConversation(conversationId);
            });

            // Check online status for all participants
            this.checkOnlineStatuses();
        }

        searchConversations(query) {
            if (!query) {
                $('.conversation-item').show();
                return;
            }

            const lowerQuery = query.toLowerCase();
            $('.conversation-item').each(function() {
                const name = $(this).find('.conversation-name').text().toLowerCase();
                const preview = $(this).find('.conversation-preview').text().toLowerCase();
                
                if (name.includes(lowerQuery) || preview.includes(lowerQuery)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        async openConversation(conversationId) {
            // Find conversation
            const conversation = this.conversations.find(c => c.conversation_id === conversationId);
            if (!conversation) return;

            // Update UI
            $('.conversation-item').removeClass('active');
            $(`.conversation-item[data-conversation-id="${conversationId}"]`).addClass('active');

            // Set current conversation
            this.currentConversation = conversation;

            // Load messages
            await this.loadMessages(conversationId);

            // Subscribe to Pusher channel
            this.subscribeToConversation(conversationId);

            // Update header
            const participant = conversation.participant;
            $('#participantAvatar').text(this.getInitials(participant.name));
            $('#participantName').text(participant.name);
            this.updateParticipantStatus(participant.user_id);

            // Show chat panel
            $('#emptyState').hide();
            $('#activeChat').css('display', 'flex');

            // Mobile: show chat panel
            if ($(window).width() < 768) {
                $('#conversationsPanel').removeClass('show');
                $('#chatPanel').addClass('show');
            }

            // Focus input
            $('#messageInput').focus();

            // Clear unread badge
            $(`.conversation-item[data-conversation-id="${conversationId}"] .unread-badge`).remove();
        }

        showConversationsList() {
            $('#chatPanel').removeClass('show');
            $('#conversationsPanel').addClass('show');
        }

        // ==================== MESSAGES ====================
        async loadMessages(conversationId) {
            try {
                const result = await this.apiCall(`/conversations/${conversationId}/messages?limit=50`);
                this.messages = result.messages || [];
                this.renderMessages();
                this.scrollToBottom();
                this.markVisibleMessagesAsRead();
            } catch (error) {
                console.error('[Chat] Failed to load messages:', error);
                $('#chatMessages').html(`
                    <div class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load messages</p>
                    </div>
                `);
            }
        }

        renderMessages() {
            const $container = $('#chatMessages');
            
            if (this.messages.length === 0) {
                $container.html(`
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat-quote" style="font-size: 48px;"></i>
                        <p class="mt-2">No messages yet. Start the conversation!</p>
                    </div>
                `);
                return;
            }

            const html = this.messages.map(msg => {
                const isSent = msg.sender_id === APP_CONFIG.currentUserId;
                const messageClass = isSent ? 'sent' : 'received';
                const time = new Date(msg.created_at).toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });

                return `
                    <div class="message ${messageClass}" data-message-id="${msg.message_id}">
                        <div class="message-bubble">
                            <div class="message-text">${this.escapeHtml(msg.message_text)}</div>
                            <div class="message-time">
                                <span>${time}</span>
                                ${isSent && msg.is_read ? '<i class="bi bi-check-all read-indicator"></i>' : ''}
                                ${isSent && !msg.is_read ? '<i class="bi bi-check"></i>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $container.html(html);
        }

        async sendMessage(messageText = null, conversationId = null) {
            const $input = $('#messageInput');
            const message = messageText || $input.val().trim();
            const targetConversation = conversationId || (this.currentConversation ? this.currentConversation.conversation_id : null);

            if (!message || !targetConversation) return;

            // Validate message length
            if (message.length > APP_CONFIG.security.maxMessageLength) {
                this.showError(`Message too long. Maximum ${APP_CONFIG.security.maxMessageLength} characters allowed.`);
                return;
            }

            // Throttle message sending
            const now = Date.now();
            if (now - this.lastMessageTime < APP_CONFIG.performance.messageThrottle) {
                console.log('[Chat] Message throttled');
                return;
            }
            this.lastMessageTime = now;

            // If offline, queue the message
            if (!this.isOnline || this.connectionState !== 'connected') {
                console.log('[Chat] Queueing message for later delivery');
                this.messageQueue.push({ text: message, conversationId: targetConversation });
                this.showError('Message queued. Will be sent when connection is restored.');
                if (!messageText) {
                    $input.val('').css('height', 'auto');
                }
                return;
            }

            // Disable button
            $('#sendButton').prop('disabled', true);

            try {
                const result = await this.apiCall('/messages', 'POST', {
                    conversation_id: targetConversation,
                    message_text: message
                });

                // Add message to UI immediately for current conversation
                if (this.currentConversation && targetConversation === this.currentConversation.conversation_id) {
                    this.messages.push(result.message);
                    this.renderMessages();
                    this.scrollToBottom();
                }

                // Clear input only if this was user-initiated
                if (!messageText) {
                    $input.val('').css('height', 'auto');
                }

                // Update conversation list
                this.updateConversationPreview(targetConversation, message);

                // Trigger Pusher event if backend doesn't handle it
                if (result.pusher_data) {
                    console.log('[Chat] Message sent successfully');
                }

            } catch (error) {
                console.error('[Chat] Failed to send message:', error);
                
                // Queue message for retry if it's a network error
                if (error.message.includes('fetch') || error.message.includes('network')) {
                    this.messageQueue.push({ text: message, conversationId: targetConversation });
                    this.showError('Message queued due to network error. Will retry automatically.');
                } else {
                    this.showError('Failed to send message. Please try again.');
                }
            } finally {
                $('#sendButton').prop('disabled', false);
                if (!messageText) {
                    $input.focus();
                }
            }
        }

        handleNewMessage(message) {
            console.log('[Chat] Processing new message:', message);
            
            // Prevent duplicate messages
            const existingMessage = this.messages.find(m => m.message_id === message.message_id);
            if (existingMessage) {
                console.log('[Chat] Duplicate message ignored:', message.message_id);
                return;
            }

            // Check if message is for current conversation
            if (this.currentConversation && 
                message.conversation_id === this.currentConversation.conversation_id) {
                
                // Don't add if it's our own message (already added locally)
                if (message.sender_id !== APP_CONFIG.currentUserId) {
                    this.messages.push(message);
                    this.renderMessages();
                    this.scrollToBottom();
                    
                    // Mark as read if conversation is open and visible
                    if (document.visibilityState === 'visible') {
                        setTimeout(() => {
                            this.markAsRead([message.message_id]);
                        }, 1000); // Delay to ensure user sees the message
                    }
                    
                    // Show notification if page is not visible
                    if (document.visibilityState === 'hidden') {
                        this.showDesktopNotification(message);
                    }
                }
            } else {
                // Message for different conversation - add to buffer
                this.addToMessageBuffer(message);
            }

            // Update conversation list
            this.updateConversationPreview(message.conversation_id, message.message_text);
            
            // Play notification sound
            this.playNotificationSound();
            
            // Update unread count
            this.updateUnreadCount(message.conversation_id);
        }

        addToMessageBuffer(message) {
            // Add to buffer for later processing
            this.messageBuffer.push(message);
            
            // Limit buffer size
            if (this.messageBuffer.length > APP_CONFIG.performance.messageBufferSize) {
                this.messageBuffer.shift(); // Remove oldest message
            }
        }

        showDesktopNotification(message) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const senderName = message.sender?.name || 'Someone';
                const notification = new Notification(`New message from ${senderName}`, {
                    body: message.message_text.substring(0, 100),
                    icon: '/images/chat-icon.png',
                    tag: `chat-${message.conversation_id}`
                });
                
                notification.onclick = () => {
                    window.focus();
                    this.openConversation(message.conversation_id);
                    notification.close();
                };
                
                setTimeout(() => notification.close(), 5000);
            }
        }

        updateUnreadCount(conversationId) {
            const $conversation = $(`.conversation-item[data-conversation-id="${conversationId}"]`);
            const $badge = $conversation.find('.unread-badge');
            
            if ($badge.length > 0) {
                const currentCount = parseInt($badge.text()) || 0;
                $badge.text(currentCount + 1);
            } else {
                $conversation.find('.conversation-meta').append('<span class="unread-badge">1</span>');
            }
        }

        // ==================== TYPING INDICATORS ====================
        handleTyping() {
            if (!this.currentConversation || this.connectionState !== 'connected') return;

            // Throttle typing indicators
            const now = Date.now();
            if (now - this.lastTypingTime < APP_CONFIG.performance.typingThrottle) {
                return;
            }
            this.lastTypingTime = now;

            // Clear previous timeout
            if (this.typingTimeout) {
                clearTimeout(this.typingTimeout);
            }

            // Send typing start event
            this.apiCall('/typing', 'POST', {
                conversation_id: this.currentConversation.conversation_id,
                is_typing: true
            }).catch(err => console.error('Failed to send typing indicator:', err));

            // Set timeout to send typing stop event
            this.typingTimeout = setTimeout(() => {
                if (this.currentConversation && this.connectionState === 'connected') {
                    this.apiCall('/typing', 'POST', {
                        conversation_id: this.currentConversation.conversation_id,
                        is_typing: false
                    }).catch(err => console.error('Failed to send typing indicator:', err));
                }
            }, 3000);
        }

        showTypingIndicator(data) {
            if (!this.currentConversation || !data.is_typing) {
                $('#typingIndicator').hide();
                return;
            }

            const userName = data.user_name || 'Someone';
            $('#typingIndicator')
                .html(`<i class="bi bi-three-dots"></i> ${this.escapeHtml(userName)} is typing...`)
                .show();
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('#typingIndicator').hide();
            }, 5000);
        }

        // ==================== READ RECEIPTS ====================
        async markVisibleMessagesAsRead() {
            if (!this.currentConversation) return;

            const unreadMessageIds = this.messages
                .filter(msg => !msg.is_read && msg.sender_id !== APP_CONFIG.currentUserId)
                .map(msg => msg.message_id);

            if (unreadMessageIds.length > 0) {
                await this.markAsRead(unreadMessageIds);
            }
        }

        async markAsRead(messageIds) {
            try {
                await this.apiCall('/messages/read', 'POST', {
                    conversation_id: this.currentConversation.conversation_id,
                    message_ids: messageIds
                });

                // Update local state
                this.messages.forEach(msg => {
                    if (messageIds.includes(msg.message_id)) {
                        msg.is_read = true;
                    }
                });
            } catch (error) {
                console.error('[Chat] Failed to mark as read:', error);
            }
        }

        updateMessageReadStatus(data) {
            // Update UI to show read receipts
            data.message_ids?.forEach(messageId => {
                const $message = $(`.message[data-message-id="${messageId}"]`);
                $message.find('.bi-check').removeClass('bi-check').addClass('bi-check-all read-indicator');
            });
        }

        // ==================== ONLINE STATUS ====================
        startOnlineStatusUpdates() {
            // Update status every 30 seconds
            this.updateOnlineStatus();
            this.onlineStatusInterval = setInterval(() => {
                this.updateOnlineStatus();
            }, 30000);
        }

        async updateOnlineStatus() {
            try {
                await this.apiCall('/online-status', 'POST', {
                    status: 'online'
                });
            } catch (error) {
                console.error('[Chat] Failed to update online status:', error);
            }
        }

        async checkOnlineStatuses() {
            const userIds = this.conversations.map(c => c.participant.user_id);
            
            if (userIds.length === 0) return;

            try {
                const result = await this.apiCall('/online-status/check', 'POST', {
                    user_ids: userIds
                });

                // Update UI
                Object.entries(result.statuses || {}).forEach(([userId, status]) => {
                    const $indicator = $(`.online-indicator[data-user-id="${userId}"]`);
                    if (status.online) {
                        $indicator.show();
                    } else {
                        $indicator.hide();
                    }
                });
            } catch (error) {
                console.error('[Chat] Failed to check online statuses:', error);
            }
        }

        updateParticipantStatus(userId) {
            // This would be called when opening a conversation
            // For now, just show as online
            $('#participantStatusText').text('Online').removeClass('offline');
        }

        // ==================== UTILITY FUNCTIONS ====================
        getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        formatTimeAgo(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
            if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
            if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
            
            return date.toLocaleDateString();
        }

        scrollToBottom() {
            const $messages = $('#chatMessages');
            $messages.scrollTop($messages[0].scrollHeight);
        }

        updateConversationPreview(conversationId, message) {
            const $conversation = $(`.conversation-item[data-conversation-id="${conversationId}"]`);
            $conversation.find('.conversation-preview').text(message.substring(0, 40) + '...');
            $conversation.find('.conversation-time').text('Just now');
            
            // Move to top of list
            $conversation.prependTo('#conversationsList');
        }

        playNotificationSound() {
            // Optional: play a subtle notification sound
            // const audio = new Audio('/sounds/notification.mp3');
            // audio.play().catch(err => console.log('Could not play sound:', err));
        }

        showError(message) {
            console.error('[Chat] Error:', message);
            
            // Show toast notification
            const toast = $(`
                <div class="toast-notification error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>${this.escapeHtml(message)}</span>
                    <button class="close-toast">×</button>
                </div>
            `);
            
            $('body').append(toast);
            toast.addClass('show');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
            
            // Manual close
            toast.find('.close-toast').on('click', () => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            });
        }

        showSuccess(message) {
            console.log('[Chat] Success:', message);
            
            const toast = $(`
                <div class="toast-notification success">
                    <i class="bi bi-check-circle"></i>
                    <span>${this.escapeHtml(message)}</span>
                    <button class="close-toast">×</button>
                </div>
            `);
            
            $('body').append(toast);
            toast.addClass('show');
            
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
            
            toast.find('.close-toast').on('click', () => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            });
        }

        // ==================== FILE ATTACHMENT SUPPORT ====================
        handleFileSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!APP_CONFIG.security.allowedFileTypes.includes(file.type)) {
                this.showError('File type not allowed. Please select an image or PDF file.');
                return;
            }

            // Validate file size
            if (file.size > APP_CONFIG.security.maxFileSize) {
                this.showError(`File too large. Maximum size is ${this.formatFileSize(APP_CONFIG.security.maxFileSize)}.`);
                return;
            }

            this.selectedFile = file;
            this.showFilePreview(file);
        }

        showFilePreview(file) {
            const $preview = $('#filePreview');
            const $fileName = $preview.find('.file-name');
            const $fileSize = $preview.find('.file-size');
            const $icon = $preview.find('i');

            $fileName.text(file.name);
            $fileSize.text(this.formatFileSize(file.size));

            // Update icon based on file type
            if (file.type.startsWith('image/')) {
                $icon.removeClass().addClass('bi bi-image');
            } else if (file.type === 'application/pdf') {
                $icon.removeClass().addClass('bi bi-file-pdf');
            } else {
                $icon.removeClass().addClass('bi bi-file-earmark');
            }

            $preview.show();
        }

        removeSelectedFile() {
            this.selectedFile = null;
            $('#filePreview').hide();
            $('#fileInput').val('');
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        async uploadFile(file, conversationId) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('conversation_id', conversationId);

            try {
                const response = await fetch(`${APP_CONFIG.apiBaseUrl}/upload`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': APP_CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'File upload failed');
                }
                
                return result;
            } catch (error) {
                console.error('[Chat] File upload error:', error);
                throw error;
            }
        }

        // ==================== NOTIFICATION SUPPORT ====================
        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('[Notifications] Permission:', permission);
                });
            }
        }

        // ==================== ENHANCED UTILITY FUNCTIONS ====================
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        }

        // ==================== CONNECTION QUALITY MONITORING ====================
        measureConnectionQuality() {
            if (!this.pusher || !this.pusher.connection) return;

            const startTime = Date.now();
            const channel = this.pusher.subscribe('test-channel');
            
            channel.bind('pusher:subscription_succeeded', () => {
                const latency = Date.now() - startTime;
                console.log(`[Performance] Connection latency: ${latency}ms`);
                
                if (latency > 1000) {
                    console.warn('[Performance] High latency detected');
                }
                
                this.pusher.unsubscribe('test-channel');
            });
        }

        updateOnlineIndicator(userId, isOnline) {
            const $indicator = $(`.online-indicator[data-user-id="${userId}"]`);
            if (isOnline) {
                $indicator.css('background', '#10B981').show();
            } else {
                $indicator.css('background', '#9CA3AF').hide();
            }
        }

        updatePresenceList(members) {
            members.each((member) => {
                console.log('[Presence] Member:', member.id, member.info);
                this.updateOnlineIndicator(member.id, true);
            });
        }

        // ==================== CLEANUP MECHANISMS ====================
        cleanup() {
            console.log('[Chat] Cleaning up resources...');
            
            // Stop online status updates
            if (this.onlineStatusInterval) {
                clearInterval(this.onlineStatusInterval);
                this.onlineStatusInterval = null;
            }

            // Unsubscribe from all channels
            this.subscribedChannels.forEach((channel, conversationId) => {
                this.unsubscribeFromConversation(conversationId);
            });

            // Disconnect Pusher
            if (this.pusher) {
                this.pusher.disconnect();
            }

            // Notify server to cleanup
            if (this.currentConversation) {
                fetch(`${APP_CONFIG.apiBaseUrl}/cleanup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': APP_CONFIG.csrfToken
                    },
                    body: JSON.stringify({
                        conversation_id: this.currentConversation.conversation_id
                    }),
                    keepalive: true // Ensure request completes even if page is closing
                }).catch(err => console.error('[Cleanup] Error:', err));
            }
        }
    }

    // Initialize chat application when document is ready
    $(document).ready(function() {
        // Check if Pusher is available
        if (typeof Pusher === 'undefined') {
            console.error('[Chat] Pusher library not loaded');
            $('#connectionStatus')
                .addClass('error')
                .html('<i class="bi bi-exclamation-triangle"></i> Pusher library not loaded')
                .show();
            return;
        }

        // Check if user is authenticated
        if (!APP_CONFIG.currentUserId) {
            console.error('[Chat] User not authenticated');
            $('#connectionStatus')
                .addClass('error')
                .html('<i class="bi bi-person-x"></i> Authentication required')
                .show();
            return;
        }

        // Initialize chat application
        window.chatApp = new ChatApplication();
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (window.chatApp) {
                window.chatApp.cleanup();
            }
        });

        // Cleanup on visibility change (tab switch)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && window.chatApp) {
                // Update status to away when tab is hidden
                window.chatApp.apiCall('/online-status', 'POST', { status: 'away' })
                    .catch(err => console.error('[Status] Failed to update:', err));
            } else if (window.chatApp) {
                // Update status to online when tab is visible
                window.chatApp.apiCall('/online-status', 'POST', { status: 'online' })
                    .catch(err => console.error('[Status] Failed to update:', err));
            }
        });
        
        // Global error handler for unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('[Chat] Unhandled promise rejection:', event.reason);
            if (window.chatApp) {
                window.chatApp.showError('An unexpected error occurred. Please refresh the page.');
            }
        });

        // Global Pusher error handler
        window.addEventListener('error', (event) => {
            if (event.message && event.message.includes('Pusher')) {
                console.error('[Pusher] Global error:', event.message);
                if (window.chatApp) {
                    window.chatApp.showError('Connection error occurred. Retrying...');
                }
            }
        });
    });

})(jQuery);
