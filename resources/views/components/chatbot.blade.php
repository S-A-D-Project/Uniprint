<!-- Chatbot Component -->
<div class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1050;">
    <!-- Chat Button -->
    <button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow-lg" style="width: 60px; height: 60px;">
        <i class="bi bi-chat-dots-fill" style="font-size: 1.5rem;"></i>
    </button>
    
    <!-- Chat Window -->
    <div id="chatbot-window" class="card shadow-lg" style="display: none; width: 350px; height: 500px; position: absolute; bottom: 80px; right: 0;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">
                    <i class="bi bi-robot me-2"></i>UniPrint Assistant
                </h6>
                <small class="opacity-75">Always here to help</small>
            </div>
            <button id="chatbot-close" class="btn btn-sm btn-light">
                <i class="bi bi-x"></i>
            </button>
        </div>
        
        <div class="card-body p-0 d-flex flex-column" style="height: calc(100% - 60px);">
            <!-- Messages Area -->
            <div id="chatbot-messages" class="flex-grow-1 p-3 overflow-auto" style="max-height: 350px;">
                <div class="d-flex justify-content-start mb-3">
                    <div class="bg-light rounded p-2" style="max-width: 80%;">
                        <small class="d-block text-muted mb-1">UniPrint Assistant</small>
                        <div>Hello! I'm here to help you with your printing needs. How can I assist you today?</div>
                        <small class="text-muted d-block mt-1">Just now</small>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="border-top p-2">
                <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-sm btn-outline-primary quick-action" data-message="What printing services do you offer?">
                        Services
                    </button>
                    <button class="btn btn-sm btn-outline-primary quick-action" data-message="How long does delivery take?">
                        Delivery
                    </button>
                    <button class="btn btn-sm btn-outline-primary quick-action" data-message="What are your payment methods?">
                        Payment
                    </button>
                    <button class="btn btn-sm btn-outline-primary quick-action" data-message="How can I track my order?">
                        Order Tracking
                    </button>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="border-top p-2">
                <div class="input-group">
                    <input type="text" id="chatbot-input" class="form-control" placeholder="Type your message...">
                    <button id="chatbot-send" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background-color: #6c757d;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}
</style>

<script>
class Chatbot {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.init();
    }
    
    init() {
        // Event listeners
        document.getElementById('chatbot-toggle').addEventListener('click', () => this.toggle());
        document.getElementById('chatbot-close').addEventListener('click', () => this.close());
        document.getElementById('chatbot-send').addEventListener('click', () => this.sendMessage());
        document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
        
        // Quick action buttons
        document.querySelectorAll('.quick-action').forEach(btn => {
            btn.addEventListener('click', () => {
                const message = btn.dataset.message;
                document.getElementById('chatbot-input').value = message;
                this.sendMessage();
            });
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        this.isOpen = true;
        const window = document.getElementById('chatbot-window');
        window.style.display = 'block';
        setTimeout(() => {
            window.style.opacity = '1';
            window.style.transform = 'translateY(0)';
        }, 10);
    }
    
    close() {
        this.isOpen = false;
        const window = document.getElementById('chatbot-window');
        window.style.opacity = '0';
        window.style.transform = 'translateY(10px)';
        setTimeout(() => {
            window.style.display = 'none';
        }, 300);
    }
    
    sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Add user message
        this.addMessage(message, 'user');
        input.value = '';
        
        // Show typing indicator
        this.showTyping();
        
        // Simulate bot response
        setTimeout(() => {
            this.hideTyping();
            const response = this.generateResponse(message);
            this.addMessage(response, 'bot');
        }, 1000 + Math.random() * 1000);
    }
    
    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `d-flex justify-content-${sender === 'user' ? 'end' : 'start'} mb-3 chat-message`;
        
        const time = new Date().toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
        
        messageDiv.innerHTML = `
            <div class="bg-${sender === 'user' ? 'primary text-white' : 'light'} rounded p-2" style="max-width: 80%;">
                <small class="d-block ${sender === 'user' ? 'text-white-50' : 'text-muted'} mb-1">
                    ${sender === 'user' ? 'You' : 'UniPrint Assistant'}
                </small>
                <div>${text}</div>
                <small class="${sender === 'user' ? 'text-white-50' : 'text-muted'} d-block mt-1">${time}</small>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        this.messages.push({ text, sender, time: new Date() });
    }
    
    showTyping() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'd-flex justify-content-start mb-3';
        typingDiv.innerHTML = `
            <div class="bg-light rounded p-2">
                <small class="d-block text-muted mb-1">UniPrint Assistant</small>
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTyping() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    generateResponse(message) {
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
            return "Signing up is free and easy! Use the Sign Up tab on the login page to register as a customer or business user.";
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
}

// Initialize chatbot when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.chatbot = new Chatbot();
});
</script>
