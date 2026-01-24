@extends('layouts.public')

@php
use Illuminate\Support\Facades\DB;
@endphp

@section('title', 'My Orders')

@section('content')
<div class="min-h-screen bg-background">
    <main class="container mx-auto px-4 py-8">
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
        'cancelled' => ['label' => 'Cancelled', 'url' => route('customer.orders', ['tab' => 'cancelled'])],
    ];
@endphp

<div class="mb-6">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-4 pt-4">
            <nav class="flex items-center gap-6 overflow-x-auto" aria-label="Orders">
                @foreach($tabLinks as $key => $tabItem)
                    <a href="{{ route('customer.orders', array_filter(['tab' => $key, 'q' => ($search ?? '') !== '' ? ($search ?? '') : null])) }}"
                       class="pb-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === $key ? 'text-primary border-primary' : 'text-gray-600 border-transparent hover:text-gray-900 hover:border-gray-200' }}">
                        {{ $tabItem['label'] }}
                    </a>
                @endforeach
            </nav>

            <form action="{{ route('customer.orders') }}" method="GET" class="hidden md:block w-full max-w-md">
                <input type="hidden" name="tab" value="{{ $activeTab }}" />
                <div class="relative">
                    <i data-lucide="search" class="h-4 w-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Search by shop, order ID, or notes"
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>
            </form>
        </div>

        <div class="px-4 pb-4 md:hidden">
            <form action="{{ route('customer.orders') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $activeTab }}" />
                <div class="relative">
                    <i data-lucide="search" class="h-4 w-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Search orders"
                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Order Statistics -->
    <div class="mb-6 text-sm text-gray-600">
        @if(($search ?? '') !== '')
            Showing results for <span class="font-medium text-gray-900">{{ $search }}</span>
            <a href="{{ route('customer.orders', ['tab' => $activeTab]) }}" class="ml-2 text-primary hover:underline">Clear</a>
        @else
            Showing <span class="font-medium text-gray-900">{{ $orders->count() }}</span> orders on this page
        @endif
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="store" class="h-4 w-4 text-primary"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-semibold text-gray-900 truncate">{{ $order->enterprise_name ?? 'Print Shop' }}</span>
                            <span class="text-xs text-gray-400">•</span>
                            <span class="text-xs text-gray-500">#{{ substr((string) $order->purchase_order_id, 0, 8) }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-500">
                            <i data-lucide="calendar" class="h-3.5 w-3.5"></i>
                            <span>
                                {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : $order->created_at->format('M d, Y H:i')) : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-sm font-medium text-primary">{{ $order->status_name ?? 'Pending' }}</span>
                </div>
            </div>

            <div class="px-4 py-4">
                @php
                    $orderItems = $orderItemsByOrder[$order->purchase_order_id] ?? collect();
                    $firstItem = $orderItems->first();
                @endphp

                @if($orderItems->count() > 0)
                    <div class="flex items-start gap-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="h-6 w-6 text-gray-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $firstItem->service_name ?? 'Service' }}</div>
                            <div class="text-sm text-gray-500 mt-0.5">
                                Qty: {{ $firstItem->quantity ?? 1 }}
                                @if($orderItems->count() > 1)
                                    <span class="ml-2">+{{ $orderItems->count() - 1 }} more item{{ ($orderItems->count() - 1) === 1 ? '' : 's' }}</span>
                                @endif
                            </div>
                            @if(!empty($order->purpose) && $order->purpose !== 'Online order via UniPrint')
                                <div class="mt-2 text-sm text-gray-600 truncate">
                                    {{ $order->purpose }}
                                </div>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xs text-gray-500">Item total</div>
                            <div class="font-semibold text-gray-900">
                                ₱{{ number_format($firstItem->total_cost ?? (($firstItem->quantity ?? 1) * ($firstItem->unit_price ?? 0)), 2) }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-gray-500">No items found</div>
                @endif
            </div>

            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button onclick="openOrderChat('{{ $order->purchase_order_id }}', '{{ $order->enterprise_id }}', '{{ $order->enterprise_name }}')"
                                class="inline-flex items-center px-3 py-2 border border-primary text-primary text-sm font-medium rounded-md hover:bg-primary hover:text-white transition-colors">
                            <i data-lucide="message-circle" class="h-4 w-4 mr-1"></i>
                            Chat
                        </button>
                        <a href="{{ route('customer.order.details', $order->purchase_order_id) }}"
                           class="inline-flex items-center px-3 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-white transition-colors js-customer-order-details">
                            <i data-lucide="eye" class="h-4 w-4 mr-1"></i>
                            View
                        </a>
                        @if(($order->status_name ?? 'Pending') === 'Delivered')
                            <form action="{{ route('customer.orders.confirm-completion', $order->purchase_order_id) }}" method="POST" onsubmit="return confirm('Confirm you received this order?');" data-up-global-loader>
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-success/30 text-success text-sm font-medium rounded-md hover:bg-success/10 transition-colors" data-up-button-loader>
                                    <i data-lucide="check-circle" class="h-4 w-4 mr-1"></i>
                                    Confirm Received
                                </button>
                            </form>
                        @endif
                        @if(($order->status_name ?? 'Pending') === 'Pending')
                            <form action="{{ route('customer.orders.cancel', $order->purchase_order_id) }}" method="POST" onsubmit="return confirm('Cancel this order?');" data-up-global-loader>
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-200 text-red-600 text-sm font-medium rounded-md hover:bg-red-50 transition-colors" data-up-button-loader>
                                    <i data-lucide="x-circle" class="h-4 w-4 mr-1"></i>
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3">
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Order total</div>
                            <div class="text-lg font-bold text-primary">
                                ₱{{ number_format($order->total ?? $order->total_order_amount ?? 0, 2) }}
                            </div>
                        </div>
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
    </main>
</div>

<x-ui.modal id="customerOrderDetailsModal" title="Order Details" size="xl" scrollable>
    <div id="customerOrderDetailsModalBody" class="min-h-[200px]"></div>
</x-ui.modal>

<!-- Chat Modal -->
@if(false)
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

@endif

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
    if (window.UniPrintChat && typeof window.UniPrintChat.openEnterpriseChat === 'function') {
        await window.UniPrintChat.openEnterpriseChat(enterpriseId);
        return;
    }

    if (enterpriseId) {
        window.location.href = `{{ url('/chat/enterprise') }}/${enterpriseId}`;
    }
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
    if (window.UniPrintChat) return;

    initializePusher();

    // Update online status periodically
    setInterval(updateOnlineStatus, 30000); // Every 30 seconds
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.UniPrintChat) return;
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
@if(false)
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
@endif
@endpush
