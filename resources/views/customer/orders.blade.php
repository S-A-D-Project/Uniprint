@extends('layouts.public')

@php
use Illuminate\Support\Facades\DB;
@endphp

@section('title', 'My Orders')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <i data-lucide="package" class="h-8 w-8 text-primary"></i>
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
    </div>
    <p class="text-gray-600 text-lg">Track and manage your orders</p>
</div>

@php
    $activeTab = $tab ?? 'all';
    $tabLinks = [
        'all' => ['label' => 'All', 'url' => route('customer.orders', ['tab' => 'all'])],
        'to_confirm' => ['label' => 'To Confirm', 'url' => route('customer.orders', ['tab' => 'to_confirm'])],
        'processing' => ['label' => 'Processing', 'url' => route('customer.orders', ['tab' => 'processing'])],
        'final_process' => ['label' => 'Final Process', 'url' => route('customer.orders', ['tab' => 'final_process'])],
        'completed' => ['label' => 'Completed', 'url' => route('customer.orders', ['tab' => 'completed'])],
    ];
@endphp

<div class="mb-6">
    <div class="inline-flex flex-wrap gap-2 bg-white p-2 rounded-lg shadow-sm">
        @foreach($tabLinks as $key => $tabItem)
            <a href="{{ $tabItem['url'] }}"
               class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === $key ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                {{ $tabItem['label'] }}
            </a>
        @endforeach
    </div>
</div>

    <!-- Order Statistics -->
    @php
        $orderStats = [
            'pending' => $orders->where('status_name', 'Pending')->count(),
            'in_progress' => $orders->where('status_name', 'Processing')->count(),
            'completed' => $orders->where('status_name', 'Delivered')->count()
        ];
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-bold text-warning">{{ $orderStats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-warning/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="h-6 w-6 text-warning"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-primary">{{ $orderStats['in_progress'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="settings" class="h-6 w-6 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-success">{{ $orderStats['completed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-3">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="receipt" class="h-6 w-6 text-primary"></i>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Order #{{ substr($order->purchase_order_id, 0, 8) }}</h3>
                                    <p class="text-gray-600 mb-2">{{ $order->enterprise_name ?? 'Unknown Shop' }}</p>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i data-lucide="calendar" class="h-4 w-4 mr-1"></i>
                                        {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : $order->created_at->format('M d, Y H:i')) : 'N/A' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Order Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $order->status_name === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($order->status_name === 'Processing' ? 'bg-blue-100 text-blue-800' : 
                                           ($order->status_name === 'Delivered' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $order->status_name ?? 'Unknown' }}
                                    </span>
                                    <!-- Chat Button -->
                                    <button onclick="openOrderChat('{{ $order->purchase_order_id }}', '{{ $order->enterprise_id }}', '{{ $order->enterprise_name }}')" 
                                            class="inline-flex items-center px-3 py-1.5 border border-primary text-primary text-sm font-medium rounded-md hover:bg-primary hover:text-white transition-colors">
                                        <i data-lucide="message-circle" class="h-4 w-4 mr-1"></i>
                                        Chat
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(!empty($order->purpose) && $order->purpose !== 'Online order via UniPrint')
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Special Instructions:</h4>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-sm text-blue-800">{{ $order->purpose }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Order Items:</h4>
                            @php
                                // Load order items with customizations
                                $orderItems = DB::table('order_items')
                                    ->join('services', 'order_items.service_id', '=', 'services.service_id')
                                    ->where('order_items.purchase_order_id', $order->purchase_order_id)
                                    ->select('order_items.*', 'services.service_name as product_name')
                                    ->get();
                            @endphp
                            @if($orderItems->count() > 0)
                                <div class="space-y-3">
                                    @foreach($orderItems as $item)
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <span class="font-medium text-gray-900">{{ $item->quantity }}x {{ $item->product_name ?? 'Unknown Product' }}</span>
                                            </div>
                                            <span class="font-medium text-gray-900">₱{{ number_format($item->total_cost ?? ($item->quantity * ($item->unit_price ?? 0)), 2) }}</span>
                                        </div>
                                        
                                        @php
                                            // Load customizations for this item
                                            $customizations = DB::table('order_item_customizations')
                                                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                                                ->where('order_item_customizations.order_item_id', $item->item_id)
                                                ->select('customization_options.option_name', 'customization_options.option_type', 'order_item_customizations.price_snapshot')
                                                ->get();
                                        @endphp
                                        
                                        @if($customizations->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($customizations as $customization)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-primary/10 text-primary rounded-md">
                                                        {{ $customization->option_type }}: {{ $customization->option_name }}
                                                        @if($customization->price_snapshot > 0)
                                                            (+₱{{ number_format($customization->price_snapshot, 2) }})
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-gray-500">No items found</div>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-1 lg:border-l lg:border-gray-200 lg:pl-6">
                        <div class="text-center mb-4">
                            @if(($order->status_name ?? $order->current_status ?? 'Pending') == 'Pending')
                                <div class="w-16 h-16 bg-warning/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="clock" class="h-8 w-8 text-warning"></i>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            @elseif(($order->status_name ?? $order->current_status ?? 'Pending') == 'In Progress')
                                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="settings" class="h-8 w-8 text-primary"></i>
                                </div>
                                <span class="status-badge status-in-progress">In Progress</span>
                            @elseif(($order->status_name ?? $order->current_status ?? 'Pending') == 'Shipped')
                                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="truck" class="h-8 w-8 text-primary"></i>
                                </div>
                                <span class="status-badge status-in-progress">Shipped</span>
                            @else
                                <div class="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="check-circle" class="h-8 w-8 text-success"></i>
                                </div>
                                <span class="status-badge status-completed">{{ $order->status_name ?? $order->current_status ?? 'Complete' }}</span>
                            @endif
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-2xl font-bold text-primary">
                                ₱{{ number_format($order->total ?? $order->total_order_amount ?? 0, 2) }}
                            </p>
                        </div>

                        <a href="{{ route('customer.order.details', $order->purchase_order_id) }}" 
                           class="customer-button-primary w-full text-center js-customer-order-details">
                            <i data-lucide="eye" class="h-4 w-4 mr-2"></i>View Details
                        </a>

                        @if(($order->status_name ?? 'Pending') === 'Pending')
                        <form action="{{ route('customer.orders.cancel', $order->purchase_order_id) }}" method="POST" class="mt-2" onsubmit="return confirm('Cancel this order?');">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-200 text-red-600 rounded-md hover:bg-red-50 transition-colors">
                                <i data-lucide="x-circle" class="h-4 w-4 mr-2"></i>Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-12 text-center">
                <i data-lucide="package-x" class="h-16 w-16 text-gray-400 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders</p>
                <a href="{{ route('customer.enterprises') }}" class="customer-button-primary">
                    <i data-lucide="shopping-bag" class="h-4 w-4 mr-2"></i>Start Shopping
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if($orders->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $orders->links() }}
    </div>
    @endif
</div>

<x-ui.modal id="customerOrderDetailsModal" title="Order Details" size="xl" scrollable>
    <div id="customerOrderDetailsModalBody" class="min-h-[200px]"></div>
</x-ui.modal>

<!-- Chat Modal -->
<div id="chatModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <!-- Chat Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                    <i data-lucide="building" class="h-5 w-5 text-primary"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900" id="chatBusinessName">Business Chat</h3>
                    <p class="text-sm text-gray-600">Order #<span id="chatOrderId"></span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div id="onlineStatus" class="flex items-center gap-2 text-sm text-gray-500">
                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                    <span>Offline</span>
                </div>
                <button onclick="closeChatModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="h-6 w-6"></i>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <div class="text-center text-gray-500 py-8">
                <i data-lucide="message-circle" class="h-12 w-12 mx-auto mb-2 text-gray-400"></i>
                <p>Start a conversation about your order</p>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typingIndicator" class="px-4 py-2 text-sm text-gray-500 italic hidden">
            <div class="flex items-center gap-2">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span id="typingUser">Someone</span> is typing...
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <textarea id="messageInput" 
                              placeholder="Type your message about this order..." 
                              rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                              onkeypress="handleMessageKeyPress(event)"></textarea>
                </div>
                <button onclick="sendMessage()" 
                        id="sendButton"
                        class="bg-primary text-white p-2 rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="send" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                <span>Press Enter to send, Shift+Enter for new line</span>
                <span id="messageCount">0/1000</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openCustomerOrderDetailsModal(url) {
    const modalEl = document.getElementById('customerOrderDetailsModal');
    const bodyEl = document.getElementById('customerOrderDetailsModalBody');
    if (!modalEl || !bodyEl) {
        window.location.href = url;
        return;
    }

    bodyEl.innerHTML = '<div class="py-5 text-center text-muted">Loading…</div>';

    let bsModal = window.modal_customerOrderDetailsModal;
    if (!bsModal && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalEl);
        window.modal_customerOrderDetailsModal = bsModal;
    }

    if (bsModal) {
        bsModal.show();
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
        .then((res) => {
            if (!res.ok) throw new Error('Failed to load order details');
            return res.text();
        })
        .then((html) => {
            bodyEl.innerHTML = html;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch((err) => {
            bodyEl.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(err.message || 'Failed to load order details')}</div>`;
        });
}

document.addEventListener('click', function (e) {
    const link = e.target.closest('a.js-customer-order-details');
    if (!link) return;
    e.preventDefault();
    openCustomerOrderDetailsModal(link.getAttribute('href'));
});

// Chat functionality variables
let currentConversationId = null;
let currentBusinessId = null;
let currentOrderId = null;
let pusher = null;
let channel = null;
let typingTimer = null;

// Initialize Pusher connection
function initializePusher() {
    if (typeof Pusher !== 'undefined') {
        pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true,
            authEndpoint: '/api/chat/pusher/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': 'Bearer ' + '{{ session('api_token') }}'
                }
            }
        });
    }
}

// Open chat modal for specific order
async function openOrderChat(orderId, enterpriseId, businessName) {
    currentOrderId = orderId;
    window.currentEnterpriseId = enterpriseId;
    
    // Update modal header
    document.getElementById('chatBusinessName').textContent = businessName;
    document.getElementById('chatOrderId').textContent = orderId.substring(0, 8);
    
    // Show modal
    document.getElementById('chatModal').classList.remove('hidden');
    
    // Initialize chat
    await initializeOrderChat(enterpriseId);
    
    // Focus message input
    document.getElementById('messageInput').focus();
}

// Initialize chat for specific order
async function initializeOrderChat(enterpriseId) {
    try {
        // Resolve enterprise owner user_id (works regardless of online status)
        const businessResponse = await fetch('/api/chat/enterprise-owner', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enterprise_id: enterpriseId })
        });

        if (!businessResponse.ok) {
            throw new Error('Failed to resolve print shop owner');
        }

        const businessData = await businessResponse.json();
        if (!businessData.success || !businessData.business_user_id) {
            showChatError(businessData.message || 'Business owner not found');
            return;
        }

        currentBusinessId = businessData.business_user_id;
        
        // Get or create conversation
        const conversationResponse = await fetch('/api/chat/conversations', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                business_id: currentBusinessId
            })
        });
        
        if (!conversationResponse.ok) {
            throw new Error('Failed to create conversation');
        }
        
        const conversationData = await conversationResponse.json();
        currentConversationId = conversationData.conversation.conversation_id;
        
        // Load existing messages
        await loadChatMessages();
        
        // Subscribe to real-time updates
        subscribeToConversation();
        
        // Update online status
        updateOnlineStatus();
        
    } catch (error) {
        console.error('Chat initialization error:', error);
        showChatError('Failed to initialize chat: ' + error.message);
    }
}

// Load chat messages
async function loadChatMessages() {
    if (!currentConversationId) return;
    
    try {
        const response = await fetch(`/api/chat/conversations/${currentConversationId}/messages`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load messages');
        }
        
        const data = await response.json();
        displayMessages(data.messages);
        
        // Mark messages as read
        if (data.messages.length > 0) {
            markMessagesAsRead();
        }
        
    } catch (error) {
        console.error('Load messages error:', error);
        showChatError('Failed to load messages');
    }
}

// Display messages in chat
function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = '';
    
    if (messages.length === 0) {
        chatMessages.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i data-lucide="message-circle" class="h-12 w-12 mx-auto mb-2 text-gray-400"></i>
                <p>Start a conversation about your order</p>
            </div>
        `;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        return;
    }
    
    messages.forEach(message => {
        const messageElement = createMessageElement(message);
        chatMessages.appendChild(messageElement);
    });
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Create message element
function createMessageElement(message) {
    const isOwn = message.sender_id === '{{ session('user_id') }}';
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
    
    const time = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
            isOwn 
                ? 'bg-primary text-white' 
                : 'bg-white border border-gray-200'
        }">
            <div class="flex items-start gap-2">
                ${!isOwn ? `
                    <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                        <i data-lucide="user" class="h-3 w-3 text-gray-600"></i>
                    </div>
                ` : ''}
                <div class="flex-1">
                    ${!isOwn ? `<p class="text-xs font-medium text-gray-600 mb-1">${message.sender.name}</p>` : ''}
                    <p class="text-sm">${escapeHtml(message.message_text)}</p>
                    <p class="text-xs mt-1 ${isOwn ? 'text-white/70' : 'text-gray-500'}">${time}</p>
                </div>
            </div>
        </div>
    `;
    
    return messageDiv;
}

// Send message
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();
    
    if (!messageText || !currentConversationId) return;
    
    const sendButton = document.getElementById('sendButton');
    sendButton.disabled = true;
    
    try {
        const response = await fetch('/api/chat/messages', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message_text: messageText,
                message_type: 'text'
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to send message');
        }
        
        // Clear input
        messageInput.value = '';
        updateMessageCount();
        
        // Stop typing indicator
        if (typingTimer) {
            clearTimeout(typingTimer);
            sendTypingIndicator(false);
        }
        
    } catch (error) {
        console.error('Send message error:', error);
        showChatError('Failed to send message');
    } finally {
        sendButton.disabled = false;
    }
}

// Handle message input key press
function handleMessageKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
    
    // Update character count
    updateMessageCount();
    
    // Send typing indicator
    sendTypingIndicator(true);
    
    // Clear typing timer
    if (typingTimer) {
        clearTimeout(typingTimer);
    }
    
    // Set new timer to stop typing indicator
    typingTimer = setTimeout(() => {
        sendTypingIndicator(false);
    }, 1000);
}

// Update message character count
function updateMessageCount() {
    const messageInput = document.getElementById('messageInput');
    const count = messageInput.value.length;
    document.getElementById('messageCount').textContent = `${count}/1000`;
    
    if (count > 1000) {
        messageInput.value = messageInput.value.substring(0, 1000);
        document.getElementById('messageCount').textContent = '1000/1000';
    }
}

// Send typing indicator
async function sendTypingIndicator(isTyping) {
    if (!currentConversationId) return;
    
    try {
        await fetch('/api/chat/typing', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                is_typing: isTyping
            })
        });
    } catch (error) {
        console.error('Typing indicator error:', error);
    }
}

// Subscribe to conversation updates
function subscribeToConversation() {
    if (!pusher || !currentConversationId) return;
    
    channel = pusher.subscribe(`conversation.${currentConversationId}`);
    
    // Listen for new messages
    channel.bind('new-message', function(data) {
        if (data.sender_id !== '{{ session('user_id') }}') {
            const messageElement = createMessageElement(data);
            document.getElementById('chatMessages').appendChild(messageElement);
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Mark as read
            markMessagesAsRead();
        }
    });
    
    // Listen for typing indicators
    channel.bind('user-typing', function(data) {
        if (data.user_id !== '{{ session('user_id') }}') {
            showTypingIndicator(data.user_name, data.is_typing);
        }
    });
}

// Show/hide typing indicator
function showTypingIndicator(userName, isTyping) {
    const indicator = document.getElementById('typingIndicator');
    const userSpan = document.getElementById('typingUser');
    
    if (isTyping) {
        userSpan.textContent = userName;
        indicator.classList.remove('hidden');
    } else {
        indicator.classList.add('hidden');
    }
}

// Mark messages as read
async function markMessagesAsRead() {
    if (!currentConversationId) return;
    
    try {
        await fetch('/api/chat/messages/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId
            })
        });
    } catch (error) {
        console.error('Mark as read error:', error);
    }
}

// Update online status
async function updateOnlineStatus() {
    if (!currentBusinessId) return;
    
    try {
        const response = await fetch('/api/chat/online-status/check', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_ids: [currentBusinessId]
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            const status = data.statuses[currentBusinessId];
            
            const statusElement = document.getElementById('onlineStatus');
            const dot = statusElement.querySelector('div');
            const text = statusElement.querySelector('span');
            
            if (status.online) {
                dot.className = 'w-2 h-2 bg-green-400 rounded-full';
                text.textContent = 'Online';
                statusElement.className = 'flex items-center gap-2 text-sm text-green-600';
            } else {
                dot.className = 'w-2 h-2 bg-gray-400 rounded-full';
                text.textContent = 'Offline';
                statusElement.className = 'flex items-center gap-2 text-sm text-gray-500';
            }
        }
    } catch (error) {
        console.error('Online status error:', error);
    }
}

// Close chat modal
function closeChatModal() {
    document.getElementById('chatModal').classList.add('hidden');
    
    // Cleanup
    if (channel) {
        pusher.unsubscribe(`conversation.${currentConversationId}`);
        channel = null;
    }
    
    currentConversationId = null;
    currentBusinessId = null;
    currentOrderId = null;
    
    // Clear typing timer
    if (typingTimer) {
        clearTimeout(typingTimer);
        typingTimer = null;
    }
}

// Show chat error
function showChatError(message) {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.innerHTML = `
        <div class="text-center text-red-500 py-8">
            <i data-lucide="alert-circle" class="h-12 w-12 mx-auto mb-2"></i>
            <p>${message}</p>
            <button onclick="closeChatModal()" class="mt-4 text-sm text-gray-600 hover:text-gray-800">Close</button>
        </div>
    `;
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializePusher();
    
    // Update online status periodically
    setInterval(updateOnlineStatus, 30000); // Every 30 seconds
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (currentConversationId) {
        fetch('/api/chat/cleanup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentConversationId
            })
        }).catch(() => {}); // Ignore errors on page unload
    }
});

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>

<!-- Pusher CDN v7.0 -->
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
@endpush
