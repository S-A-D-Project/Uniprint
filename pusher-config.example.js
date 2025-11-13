/**
 * Pusher Configuration Template
 * Copy this file and update with your actual Pusher credentials
 */

// Example Pusher Configuration
const PUSHER_CONFIG = {
    // Required: Your Pusher App Credentials
    key: 'your-pusher-app-key',           // Get from Pusher Dashboard
    cluster: 'us2',                       // Your app's cluster (us2, eu, ap1, etc.)
    
    // Connection Settings
    enabledTransports: ['ws', 'wss'],     // WebSocket transports
    forceTLS: true,                       // Force secure connections
    
    // Authentication (for private channels)
    authEndpoint: '/api/chat/pusher/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    },
    
    // Connection Timeouts
    activityTimeout: 30000,               // 30 seconds
    pongTimeout: 6000,                    // 6 seconds
    
    // Optional: Additional Settings
    enableLogging: true,                  // Enable Pusher debug logging
    logToConsole: true,                   // Log events to browser console
};

// Performance Configuration
const PERFORMANCE_CONFIG = {
    messageThrottle: 100,                 // Minimum ms between message sends
    typingThrottle: 1000,                 // Minimum ms between typing indicators
    reconnectDelay: 2000,                 // Initial reconnection delay (ms)
    maxReconnectAttempts: 5,              // Maximum reconnection attempts
    messageBufferSize: 100,               // Maximum messages to buffer when offline
    channelTimeout: 30000,                // Channel subscription timeout (ms)
    
    // Connection Quality Thresholds
    goodLatencyThreshold: 200,            // ms - considered good connection
    poorLatencyThreshold: 1000,           // ms - considered poor connection
    
    // Memory Management
    maxConversationsInMemory: 50,         // Maximum conversations to keep in memory
    messageHistoryLimit: 200,             // Maximum messages per conversation in memory
};

// Security Configuration
const SECURITY_CONFIG = {
    maxMessageLength: 5000,               // Maximum characters per message
    
    // File Upload Settings
    allowedFileTypes: [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ],
    maxFileSize: 10 * 1024 * 1024,        // 10MB maximum file size
    
    // Rate Limiting
    maxMessagesPerMinute: 60,             // Maximum messages per user per minute
    maxTypingEventsPerMinute: 30,         // Maximum typing events per user per minute
    
    // Content Filtering
    enableProfanityFilter: false,         // Enable basic profanity filtering
    enableLinkValidation: true,           // Validate URLs in messages
};

// UI Configuration
const UI_CONFIG = {
    // Theme Settings
    theme: 'light',                       // 'light', 'dark', or 'auto'
    primaryColor: '#4F46E5',              // Primary brand color
    
    // Notification Settings
    enableDesktopNotifications: true,     // Request notification permission
    enableSoundNotifications: false,      // Play sound on new messages
    notificationDuration: 5000,           // Notification display duration (ms)
    
    // Animation Settings
    enableAnimations: true,               // Enable UI animations
    messageAnimationDuration: 300,        // Message appearance animation (ms)
    typingAnimationSpeed: 1500,           // Typing indicator animation speed (ms)
    
    // Auto-scroll Settings
    autoScrollThreshold: 100,             // Pixels from bottom to trigger auto-scroll
    smoothScrollDuration: 300,            // Smooth scroll animation duration (ms)
};

// Development/Debug Configuration
const DEBUG_CONFIG = {
    enableDebugMode: false,               // Enable debug logging
    logPusherEvents: true,                // Log all Pusher events
    logApiCalls: true,                    // Log API requests/responses
    logPerformanceMetrics: false,         // Log performance measurements
    
    // Mock Data (for testing without backend)
    enableMockMode: false,                // Use mock data instead of API calls
    mockLatency: 500,                     // Simulated API latency (ms)
    
    // Error Simulation (for testing error handling)
    simulateConnectionErrors: false,      // Randomly simulate connection errors
    simulateApiErrors: false,             // Randomly simulate API errors
    errorSimulationRate: 0.1,             // Probability of simulated errors (0-1)
};

// Export configuration (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        PUSHER_CONFIG,
        PERFORMANCE_CONFIG,
        SECURITY_CONFIG,
        UI_CONFIG,
        DEBUG_CONFIG
    };
}

// Usage Instructions:
// 1. Copy this file to pusher-config.js
// 2. Update PUSHER_CONFIG with your actual Pusher credentials
// 3. Adjust other settings as needed for your environment
// 4. Include the config in your chat application

// Example Integration:
/*
// In your chat-app.js file:
const APP_CONFIG = {
    apiBaseUrl: '/api/chat',
    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    currentUserId: window.Laravel?.user?.id || null,
    currentUserName: window.Laravel?.user?.name || 'User',
    pusher: PUSHER_CONFIG,
    performance: PERFORMANCE_CONFIG,
    security: SECURITY_CONFIG,
    ui: UI_CONFIG,
    debug: DEBUG_CONFIG
};
*/

// Cluster Options:
// - us2: US East (Virginia)
// - us3: US West (Oregon)  
// - eu: Europe (Ireland)
// - ap1: Asia Pacific (Singapore)
// - ap2: Asia Pacific (Mumbai)
// - ap3: Asia Pacific (Tokyo)
// - ap4: Asia Pacific (Sydney)

// Free Tier Limits:
// - 200,000 messages per day
// - 100 concurrent connections
// - Unlimited channels
// - 7-day message history in Pusher Debug Console

// Paid Tier Benefits:
// - Higher message limits
// - More concurrent connections
// - Priority support
// - Advanced features (webhooks, etc.)
