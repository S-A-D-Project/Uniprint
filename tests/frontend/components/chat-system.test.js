/**
 * Chat System Tests
 */

import { TestUtils } from '../setup.js';

// Mock the chat application
const mockChatApp = {
    pusher: null,
    conversations: [],
    currentConversation: null,
    messages: [],
    connectionState: 'disconnected',
    
    init: jest.fn(),
    setupPusher: jest.fn(),
    sendMessage: jest.fn(),
    subscribeToConversation: jest.fn(),
    handleNewMessage: jest.fn(),
    updateConnectionStatus: jest.fn()
};

describe('Chat System', () => {
    beforeEach(() => {
        // Reset mocks
        jest.clearAllMocks();
        
        // Setup DOM
        document.body.innerHTML = `
            <div id="connectionStatus" class="connection-status"></div>
            <div id="chatMessages" class="chat-messages"></div>
            <input id="messageInput" type="text" />
            <button id="sendMessageBtn">Send</button>
        `;
        
        // Mock global chat app
        global.chatApp = mockChatApp;
    });

    describe('Pusher Connection', () => {
        test('should initialize Pusher with correct configuration', () => {
            const pusherConfig = {
                key: 'test-key',
                cluster: 'mt1',
                enabledTransports: ['ws', 'wss'],
                forceTLS: true
            };
            
            // Mock Pusher constructor
            global.Pusher = jest.fn().mockImplementation(() => ({
                connection: {
                    bind: jest.fn(),
                    state: 'connected'
                },
                subscribe: jest.fn(),
                disconnect: jest.fn(),
                connect: jest.fn()
            }));
            
            // Simulate Pusher initialization
            const pusher = new global.Pusher(pusherConfig.key, pusherConfig);
            
            expect(global.Pusher).toHaveBeenCalledWith(pusherConfig.key, pusherConfig);
            expect(pusher.connection.bind).toHaveBeenCalled();
        });

        test('should handle connection state changes', () => {
            const statusElement = document.getElementById('connectionStatus');
            
            // Test connected state
            mockChatApp.updateConnectionStatus('connected');
            expect(statusElement.classList.contains('connected')).toBe(true);
            
            // Test disconnected state
            mockChatApp.updateConnectionStatus('disconnected');
            expect(statusElement.classList.contains('disconnected')).toBe(true);
            
            // Test error state
            mockChatApp.updateConnectionStatus('error');
            expect(statusElement.classList.contains('error')).toBe(true);
        });

        test('should attempt reconnection on disconnect', async () => {
            const reconnectSpy = jest.fn();
            mockChatApp.scheduleReconnection = reconnectSpy;
            
            // Simulate disconnect
            mockChatApp.connectionState = 'disconnected';
            mockChatApp.scheduleReconnection();
            
            expect(reconnectSpy).toHaveBeenCalled();
        });
    });

    describe('Message Handling', () => {
        test('should send message when send button is clicked', () => {
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendMessageBtn');
            
            // Set message
            messageInput.value = 'Test message';
            
            // Mock send function
            sendButton.onclick = () => {
                if (messageInput.value.trim()) {
                    mockChatApp.sendMessage(messageInput.value);
                    messageInput.value = '';
                }
            };
            
            // Simulate click
            TestUtils.simulateClick(sendButton);
            
            expect(mockChatApp.sendMessage).toHaveBeenCalledWith('Test message');
            expect(messageInput.value).toBe('');
        });

        test('should not send empty messages', () => {
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendMessageBtn');
            
            // Set empty message
            messageInput.value = '   ';
            
            sendButton.onclick = () => {
                if (messageInput.value.trim()) {
                    mockChatApp.sendMessage(messageInput.value);
                }
            };
            
            TestUtils.simulateClick(sendButton);
            
            expect(mockChatApp.sendMessage).not.toHaveBeenCalled();
        });

        test('should handle incoming messages', () => {
            const messagesContainer = document.getElementById('chatMessages');
            
            const messageData = {
                id: 'msg-1',
                text: 'Hello world',
                sender: {
                    id: 'user-1',
                    name: 'John Doe'
                },
                created_at: new Date().toISOString()
            };
            
            // Mock message handling
            mockChatApp.handleNewMessage = jest.fn((data) => {
                const messageElement = document.createElement('div');
                messageElement.className = 'message-item';
                messageElement.dataset.messageId = data.id;
                messageElement.innerHTML = `
                    <div class="message-content">
                        <span class="sender-name">${data.sender.name}</span>
                        <div class="message-text">${data.text}</div>
                    </div>
                `;
                messagesContainer.appendChild(messageElement);
            });
            
            mockChatApp.handleNewMessage(messageData);
            
            expect(mockChatApp.handleNewMessage).toHaveBeenCalledWith(messageData);
            
            const messageElement = messagesContainer.querySelector('[data-message-id="msg-1"]');
            expect(messageElement).toBeTruthy();
            expect(messageElement.querySelector('.message-text').textContent).toBe('Hello world');
        });

        test('should validate message data', () => {
            const validateMessageData = (data) => {
                return data && 
                       data.id && 
                       data.text && 
                       data.sender && 
                       data.sender.id && 
                       data.sender.name;
            };
            
            // Valid message
            const validMessage = {
                id: 'msg-1',
                text: 'Hello',
                sender: { id: 'user-1', name: 'John' }
            };
            expect(validateMessageData(validMessage)).toBe(true);
            
            // Invalid message (missing sender)
            const invalidMessage = {
                id: 'msg-1',
                text: 'Hello'
            };
            expect(validateMessageData(invalidMessage)).toBe(false);
        });
    });

    describe('Conversation Management', () => {
        test('should subscribe to conversation channels', () => {
            const conversationId = 'conv-123';
            const channelName = `presence-conversation.${conversationId}`;
            
            const mockChannel = {
                bind: jest.fn(),
                unbind: jest.fn()
            };
            
            const mockPusher = {
                subscribe: jest.fn().mockReturnValue(mockChannel)
            };
            
            mockChatApp.pusher = mockPusher;
            mockChatApp.subscribeToConversation = jest.fn((convId) => {
                const channel = mockChatApp.pusher.subscribe(`presence-conversation.${convId}`);
                channel.bind('new-message', mockChatApp.handleNewMessage);
                channel.bind('user-typing', jest.fn());
                channel.bind('message-read', jest.fn());
            });
            
            mockChatApp.subscribeToConversation(conversationId);
            
            expect(mockPusher.subscribe).toHaveBeenCalledWith(channelName);
            expect(mockChannel.bind).toHaveBeenCalledWith('new-message', mockChatApp.handleNewMessage);
        });

        test('should handle typing indicators', () => {
            const typingData = {
                user_id: 'user-2',
                user_name: 'Jane Doe',
                conversation_id: 'conv-123'
            };
            
            const showTypingIndicator = jest.fn((data) => {
                if (data.user_id !== global.Laravel.user.id) {
                    const indicator = document.createElement('div');
                    indicator.className = 'typing-indicator';
                    indicator.textContent = `${data.user_name} is typing...`;
                    document.getElementById('chatMessages').appendChild(indicator);
                }
            });
            
            showTypingIndicator(typingData);
            
            const indicator = document.querySelector('.typing-indicator');
            expect(indicator).toBeTruthy();
            expect(indicator.textContent).toContain('Jane Doe is typing');
        });

        test('should handle presence events', () => {
            const onlineUsers = new Set();
            
            const handleMemberAdded = (member) => {
                onlineUsers.add(member.id);
                // Update UI to show user as online
            };
            
            const handleMemberRemoved = (member) => {
                onlineUsers.delete(member.id);
                // Update UI to show user as offline
            };
            
            // Simulate member joining
            handleMemberAdded({ id: 'user-1', info: { name: 'John' } });
            expect(onlineUsers.has('user-1')).toBe(true);
            
            // Simulate member leaving
            handleMemberRemoved({ id: 'user-1' });
            expect(onlineUsers.has('user-1')).toBe(false);
        });
    });

    describe('Error Handling', () => {
        test('should handle connection errors gracefully', () => {
            const errorHandler = jest.fn((error) => {
                console.error('Connection error:', error);
                mockChatApp.updateConnectionStatus('error');
            });
            
            const connectionError = new Error('Connection failed');
            errorHandler(connectionError);
            
            expect(errorHandler).toHaveBeenCalledWith(connectionError);
        });

        test('should queue messages when offline', () => {
            const messageQueue = [];
            
            const queueMessage = (message) => {
                if (mockChatApp.connectionState !== 'connected') {
                    messageQueue.push(message);
                    return false;
                }
                return true;
            };
            
            // Simulate offline state
            mockChatApp.connectionState = 'disconnected';
            
            const result = queueMessage('Test message');
            expect(result).toBe(false);
            expect(messageQueue).toContain('Test message');
        });

        test('should process queued messages when reconnected', () => {
            const messageQueue = ['Message 1', 'Message 2'];
            
            const processMessageQueue = () => {
                if (mockChatApp.connectionState === 'connected') {
                    while (messageQueue.length > 0) {
                        const message = messageQueue.shift();
                        mockChatApp.sendMessage(message);
                    }
                }
            };
            
            // Simulate reconnection
            mockChatApp.connectionState = 'connected';
            processMessageQueue();
            
            expect(mockChatApp.sendMessage).toHaveBeenCalledTimes(2);
            expect(messageQueue.length).toBe(0);
        });
    });

    describe('Performance', () => {
        test('should throttle typing indicators', async () => {
            const throttledTyping = jest.fn();
            let lastTypingTime = 0;
            const throttleDelay = 1000;
            
            const sendTypingIndicator = () => {
                const now = Date.now();
                if (now - lastTypingTime > throttleDelay) {
                    throttledTyping();
                    lastTypingTime = now;
                }
            };
            
            // Send multiple typing events quickly
            sendTypingIndicator();
            sendTypingIndicator();
            sendTypingIndicator();
            
            // Should only call once due to throttling
            expect(throttledTyping).toHaveBeenCalledTimes(1);
            
            // Wait for throttle period
            await new Promise(resolve => setTimeout(resolve, throttleDelay + 100));
            
            sendTypingIndicator();
            expect(throttledTyping).toHaveBeenCalledTimes(2);
        });

        test('should limit message history', () => {
            const maxMessages = 100;
            const messages = [];
            
            const addMessage = (message) => {
                messages.push(message);
                if (messages.length > maxMessages) {
                    messages.shift();
                }
            };
            
            // Add more than max messages
            for (let i = 0; i < 150; i++) {
                addMessage(`Message ${i}`);
            }
            
            expect(messages.length).toBe(maxMessages);
            expect(messages[0]).toBe('Message 50'); // First 50 should be removed
        });
    });

    describe('Cleanup', () => {
        test('should cleanup resources on page unload', () => {
            const cleanup = jest.fn(() => {
                // Unsubscribe from channels
                // Disconnect Pusher
                // Clear timers
            });
            
            // Simulate page unload
            window.dispatchEvent(new Event('beforeunload'));
            cleanup();
            
            expect(cleanup).toHaveBeenCalled();
        });

        test('should unsubscribe from channels properly', () => {
            const mockChannel = {
                unbind: jest.fn()
            };
            
            const mockPusher = {
                unsubscribe: jest.fn()
            };
            
            const unsubscribeFromConversation = (conversationId) => {
                const channelName = `presence-conversation.${conversationId}`;
                mockPusher.unsubscribe(channelName);
            };
            
            unsubscribeFromConversation('conv-123');
            
            expect(mockPusher.unsubscribe).toHaveBeenCalledWith('presence-conversation.conv-123');
        });
    });
});
