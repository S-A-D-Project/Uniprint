@extends('layouts.customer-layout')

@section('title', 'My Orders')
@section('page_title', 'My Orders')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">My Orders</h2>
            <p class="text-gray-600">Track and manage your printing orders</p>
        </div>
        <div class="flex items-center gap-3">
            <select class="customer-input">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="delivered">Delivered</option>
            </select>
            <button class="customer-button-secondary">
                <i data-lucide="download" class="h-4 w-4 inline mr-2"></i>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Order Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900">{{ $orders->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <i data-lucide="package" class="h-6 w-6 text-gray-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-warning">{{ $orders->where('status_name', 'Pending')->count() }}</p>
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
                <p class="text-2xl font-bold text-primary">{{ $orders->where('status_name', 'In Progress')->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <i data-lucide="printer" class="h-6 w-6 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Completed</p>
                <p class="text-2xl font-bold text-success">{{ $orders->whereIn('status_name', ['Delivered', 'Ready for Pickup'])->count() }}</p>
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
    <div class="bg-white rounded-lg shadow-sm overflow-hidden customer-card">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i data-lucide="receipt" class="h-6 w-6 text-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            Order #{{ substr($order->purchase_order_id, 0, 8) }}
                        </h3>
                        <p class="text-gray-600 mb-2">{{ $order->enterprise_name ?? 'Unknown Shop' }}</p>
                        <p class="text-sm text-gray-500">
                            <i data-lucide="calendar" class="h-4 w-4 inline mr-1"></i>
                            {{ isset($order->order_creation_date) ? (is_string($order->order_creation_date) ? date('M d, Y H:i', strtotime($order->order_creation_date)) : $order->order_creation_date->format('M d, Y H:i')) : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $order->status_name ?? 'pending')) }}">
                        {{ $order->status_name ?? 'Pending' }}
                    </span>
                    <p class="text-lg font-bold text-gray-900 mt-2">₱{{ number_format($order->total ?? 0, 2) }}</p>
                </div>
            </div>

            <!-- Order Items -->
            @if(isset($order->items) && $order->items->count() > 0)
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Order Items</h4>
                <div class="space-y-2">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                                <i data-lucide="file-text" class="h-4 w-4 text-gray-500"></i>
                            </div>
                            <span class="text-gray-900">{{ $item->product_name ?? 'Unknown Product' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-600">Qty: {{ $item->quantity ?? 1 }}</span>
                            <span class="text-gray-900 font-medium ml-3">₱{{ number_format($item->total_price ?? 0, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if(isset($order->tracking_number))
                        <span class="text-sm text-gray-600">
                            <i data-lucide="truck" class="h-4 w-4 inline mr-1"></i>
                            Tracking: {{ $order->tracking_number }}
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('customer.order-details', $order->purchase_order_id) }}" 
                           class="customer-button-secondary text-sm px-4 py-2">
                            View Details
                        </a>
                        @if(in_array($order->status_name, ['Pending', 'In Progress']))
                        <button onclick="trackOrder('{{ $order->purchase_order_id }}')" 
                                class="customer-button-primary text-sm px-4 py-2">
                            Track Order
                        </button>
                        @endif
                        @if($order->status_name === 'Delivered')
                        <button onclick="reorder('{{ $order->purchase_order_id }}')" 
                                class="customer-button-primary text-sm px-4 py-2">
                            Reorder
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="package" class="h-8 w-8 text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
        <p class="text-gray-600 mb-6">Start browsing our services to place your first order</p>
        <a href="{{ route('customer.dashboard') }}" class="customer-button-primary">
            Browse Services
        </a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
<div class="mt-8 flex justify-center">
    <nav class="flex items-center gap-2">
        @if($orders->onFirstPage())
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $orders->previousPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">Previous</a>
        @endif

        @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
            @if($page == $orders->currentPage())
                <span class="px-3 py-2 bg-primary text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">{{ $page }}</a>
            @endif
        @endforeach

        @if($orders->hasMorePages())
            <a href="{{ $orders->nextPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">Next</a>
        @else
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">Next</span>
        @endif
    </nav>
</div>
@endif
@endsection

@push('scripts')
<script>
function trackOrder(orderId) {
    CustomerLayout.showLoading();
    
    CustomerLayout.request(`/api/customer/orders/${orderId}/track`)
        .then(data => {
            if (data.success) {
                // Show tracking modal or redirect to tracking page
                window.location.href = `/customer/orders/${orderId}/track`;
            } else {
                CustomerLayout.showNotification(data.message || 'Failed to track order', 'error');
            }
        })
        .catch(error => {
            CustomerLayout.showNotification('Failed to track order', 'error');
        })
        .finally(() => {
            CustomerLayout.hideLoading();
        });
}

function reorder(orderId) {
    if (confirm('Are you sure you want to reorder this item?')) {
        CustomerLayout.showLoading();
        
        CustomerLayout.request(`/api/customer/orders/${orderId}/reorder`, {
            method: 'POST'
        })
            .then(data => {
                if (data.success) {
                    CustomerLayout.showNotification('Order added to cart successfully!');
                    setTimeout(() => {
                        window.location.href = '/cart';
                    }, 1500);
                } else {
                    CustomerLayout.showNotification(data.message || 'Failed to reorder', 'error');
                }
            })
            .catch(error => {
                CustomerLayout.showNotification('Failed to reorder', 'error');
            })
            .finally(() => {
                CustomerLayout.hideLoading();
            });
    }
}

// Filter orders
document.querySelector('select').addEventListener('change', function() {
    const status = this.value;
    const url = new URL(window.location);
    
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    
    window.location.href = url.toString();
});
</script>
@endpush
