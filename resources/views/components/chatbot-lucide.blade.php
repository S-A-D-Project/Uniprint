<!-- Modern Chatbot Component with Lucide Icons -->
<div class="fixed bottom-6 right-6 z-50" x-data="{ open: false }">
    <!-- Chat Toggle Button -->
    <button @click="open = !open" 
            class="w-14 h-14 bg-primary text-primary-foreground rounded-full shadow-lg hover:shadow-glow transition-smooth flex items-center justify-center relative">
        <i data-lucide="message-circle" class="h-6 w-6" x-show="!open"></i>
        <i data-lucide="x" class="h-6 w-6" x-show="open"></i>
        <span class="absolute -top-1 -right-1 bg-success w-3 h-3 rounded-full"></span>
    </button>
    
    <!-- Chat Window -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
         class="absolute bottom-16 right-0 w-80 bg-card border border-border rounded-xl shadow-card-hover overflow-hidden">
        
        <!-- Chat Header -->
        <div class="gradient-primary text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <i data-lucide="bot" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <h3 class="font-bold">UniPrint Assistant</h3>
                        <p class="text-xs text-white/80">Always here to help</p>
                    </div>
                </div>
                <button @click="open = false" class="text-white/80 hover:text-white transition-smooth">
                    <i data-lucide="minimize-2" class="h-5 w-5"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div class="h-80 overflow-y-auto p-4 space-y-4" id="chatbot-messages">
            <!-- Welcome Message -->
            <div class="flex gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="bot" class="h-4 w-4 text-primary"></i>
                </div>
                <div class="bg-secondary/50 rounded-lg p-3 max-w-[80%]">
                    <p class="text-sm">Hello! I'm here to help you with your printing needs. How can I assist you today?</p>
                    <p class="text-xs text-muted-foreground mt-1">Just now</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="border-t border-border p-3">
            <div class="flex flex-wrap gap-2">
                <button class="quick-action px-3 py-1 text-xs bg-primary/10 text-primary rounded-full hover:bg-primary/20 transition-smooth"
                        onclick="sendQuickMessage('What printing services do you offer?')">
                    Services
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-primary/10 text-primary rounded-full hover:bg-primary/20 transition-smooth"
                        onclick="sendQuickMessage('How long does delivery take?')">
                    Delivery
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-primary/10 text-primary rounded-full hover:bg-primary/20 transition-smooth"
                        onclick="sendQuickMessage('What are your payment methods?')">
                    Payment
                </button>
                <button class="quick-action px-3 py-1 text-xs bg-primary/10 text-primary rounded-full hover:bg-primary/20 transition-smooth"
                        onclick="sendQuickMessage('How can I track my order?')">
                    Order Tracking
                </button>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="border-t border-border p-3">
            <div class="flex gap-2">
                <input type="text" 
                       id="chatbot-input" 
                       placeholder="Type your message..." 
                       class="flex-1 px-3 py-2 text-sm border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                       @keydown.enter="sendMessage()">
                <button onclick="sendMessage()" 
                        class="px-3 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-smooth">
                    <i data-lucide="send" class="h-4 w-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.typing-indicator span {
    width: 6px;
    height: 6px;
    background-color: hsl(240 4.8% 95.9%);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}
</style>

<script>
function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message
    addMessage(message, 'user');
    input.value = '';
    
    // Show typing indicator
    showTyping();
    
    // Simulate bot response
    setTimeout(() => {
        hideTyping();
        const response = generateResponse(message);
        addMessage(response, 'bot');
    }, 1000 + Math.random() * 1000);
}

function sendQuickMessage(message) {
    document.getElementById('chatbot-input').value = message;
    sendMessage();
}

function addMessage(text, sender) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message flex gap-3 ${sender === 'user' ? 'flex-row-reverse' : ''}`;
    
    const time = new Date().toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="bg-primary text-primary-foreground rounded-lg p-3 max-w-[80%]">
                <p class="text-sm">${text}</p>
                <p class="text-xs opacity-70 mt-1">${time}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                <i data-lucide="bot" class="h-4 w-4 text-primary"></i>
            </div>
            <div class="bg-secondary/50 rounded-lg p-3 max-w-[80%]">
                <p class="text-sm">${text}</p>
                <p class="text-xs text-muted-foreground mt-1">${time}</p>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Re-initialize Lucide icons for new elements
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function showTyping() {
    const messagesContainer = document.getElementById('chatbot-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'flex gap-3';
    typingDiv.innerHTML = `
        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="bot" class="h-4 w-4 text-primary"></i>
        </div>
        <div class="bg-secondary/50 rounded-lg p-3">
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function hideTyping() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

function generateResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    // Service-related responses
    if (lowerMessage.includes('service') || lowerMessage.includes('offer') || lowerMessage.includes('print')) {
        return "We offer a wide range of printing services including business cards, flyers, posters, banners, t-shirts, and large format printing. Our enterprise partners provide professional quality with fast turnaround times.";
    }
    
    // Delivery-related responses
    if (lowerMessage.includes('delivery') || lowerMessage.includes('shipping') || lowerMessage.includes('how long')) {
        return "We offer multiple delivery options: Standard (5-7 business days), Express (2-3 business days), and Same Day delivery for Metro Manila. Shipping costs vary from ₱100-₱300 depending on your location and speed preference.";
    }
    
    // Payment-related responses
    if (lowerMessage.includes('payment') || lowerMessage.includes('pay') || lowerMessage.includes('method')) {
        return "We accept multiple payment methods: GCash, cash on delivery, bank transfers, and credit/debit cards. Payment is processed securely through our platform.";
    }
    
    // Order tracking responses
    if (lowerMessage.includes('track') || lowerMessage.includes('order status') || lowerMessage.includes('where is my order')) {
        return "You can track your order status in real-time through your dashboard. Go to 'My Orders' section to see current status, estimated delivery date, and order history. You'll also receive email notifications for status updates.";
    }
    
    // Pricing responses
    if (lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('how much')) {
        return "Pricing varies based on the service, quantity, materials, and customization options. You can see exact pricing when you browse services or add items to your cart. We also offer bulk discounts for larger orders.";
    }
    
    // Design-related responses
    if (lowerMessage.includes('design') || lowerMessage.includes('custom') || lowerMessage.includes('artwork')) {
        return "We offer AI-powered design tools to help you create professional designs. You can also upload your own files or work with our design partners. Popular formats include PDF, PNG, and AI files.";
    }
    
    // Account-related responses
    if (lowerMessage.includes('account') || lowerMessage.includes('register') || lowerMessage.includes('sign up')) {
        return "Creating an account is free and easy! Click the 'Sign Up' button to register as a customer. Business users can register as enterprise partners to offer printing services.";
    }
    
    // Help/support responses
    if (lowerMessage.includes('help') || lowerMessage.includes('support') || lowerMessage.includes('contact')) {
        return "I'm here to help! You can also reach our support team at support@uniprint.com or call us at (02) 123-4567 during business hours (9 AM - 6 PM, Monday to Friday).";
    }
    
    // Default responses
    const defaultResponses = [
        "That's a great question! Let me help you with that. Could you provide more details about what specific information you need?",
        "I'd be happy to assist you! Our services include business cards, flyers, posters, t-shirts, and more. What type of printing are you interested in?",
        "Thank you for your inquiry! You can browse our available services, or let me know what specific information you need about UniPrint.",
        "I'm here to help with all your printing needs! Feel free to ask about our services, pricing, delivery options, or order tracking."
    ];
    
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
}

// Initialize Lucide icons when component loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
