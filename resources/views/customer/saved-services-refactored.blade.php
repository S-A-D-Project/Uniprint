@extends('layouts.customer-layout')

@section('title', 'Saved Services')
@section('page_title', 'Saved Services')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Saved Services</h2>
            <p class="text-gray-600">Your favorite printing services for quick access</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600">{{ $savedServices->count() }} saved services</span>
            @if($savedServices->count() > 0)
            <button onclick="clearAllSaved()" class="customer-button-secondary text-red-600 border-red-200 hover:bg-red-50">
                <i data-lucide="trash-2" class="h-4 w-4 inline mr-2"></i>
                Clear All
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Quick Actions -->
@if($savedServices->count() > 0)
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h3 class="font-medium text-gray-900">Quick Actions</h3>
            <div class="flex items-center gap-2">
                <button onclick="selectAll()" class="text-sm text-primary hover:text-primary/80">Select All</button>
                <span class="text-gray-300">|</span>
                <button onclick="selectNone()" class="text-sm text-primary hover:text-primary/80">Select None</button>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="addSelectedToCart()" class="customer-button-primary" disabled id="add-to-cart-btn">
                <i data-lucide="shopping-cart" class="h-4 w-4 inline mr-2"></i>
                Add Selected to Cart
            </button>
        </div>
    </div>
</div>
@endif

<!-- Saved Services Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($savedServices as $savedService)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden customer-card" data-service-id="{{ $savedService->service_id }}">
        <!-- Selection Checkbox -->
        <div class="absolute top-3 left-3 z-10">
            <input type="checkbox" class="service-checkbox rounded text-primary focus:ring-primary" 
                   value="{{ $savedService->service_id }}" onchange="updateSelectionCount()">
        </div>

        <!-- Remove Button -->
        <div class="absolute top-3 right-3 z-10">
            <button onclick="removeSavedService('{{ $savedService->service_id }}')" 
                    class="w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-red-600 transition-colors">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <!-- Service Image/Icon -->
        <div class="aspect-video bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center relative">
            @if(isset($savedService->image_url) && $savedService->image_url)
                <img src="{{ $savedService->image_url }}" alt="{{ $savedService->service_name }}" 
                     class="w-full h-full object-cover">
            @else
                <i data-lucide="printer" class="h-12 w-12 text-primary"></i>
            @endif
            
            <!-- Popular Badge -->
            @if($savedService->is_popular ?? false)
            <span class="absolute top-2 right-2 px-2 py-1 bg-primary text-white text-xs rounded-full">
                Popular
            </span>
            @endif
        </div>

        <!-- Service Details -->
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 line-clamp-1">{{ $savedService->service_name }}</h3>
                    <p class="text-sm text-gray-600">{{ $savedService->enterprise_name ?? 'UniPrint Partner' }}</p>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                {{ $savedService->description ?? 'Professional printing service with fast delivery and high quality results.' }}
            </p>

            <!-- Pricing and Details -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="text-lg font-bold text-primary">₱{{ number_format($savedService->base_price ?? 0, 2) }}</span>
                    @if(isset($savedService->original_price) && $savedService->original_price > $savedService->base_price)
                    <span class="text-sm text-gray-500 line-through ml-2">₱{{ number_format($savedService->original_price, 2) }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 text-sm text-gray-500">
                    <i data-lucide="clock" class="h-3 w-3"></i>
                    <span>{{ $savedService->delivery_time ?? '3-5 days' }}</span>
                </div>
            </div>

            <!-- Rating (if available) -->
            @if(isset($savedService->rating) && $savedService->rating > 0)
            <div class="flex items-center gap-1 mb-3">
                @for($i = 1; $i <= 5; $i++)
                    <i data-lucide="star" class="h-3 w-3 {{ $i <= $savedService->rating ? 'text-yellow-400 fill-current' : 'text-gray-300' }}"></i>
                @endfor
                <span class="text-xs text-gray-600 ml-1">({{ $savedService->review_count ?? 0 }})</span>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button onclick="viewServiceDetails('{{ $savedService->service_id }}')" 
                        class="flex-1 customer-button-secondary text-sm px-3 py-2">
                    View Details
                </button>
                <button onclick="orderService('{{ $savedService->service_id }}')" 
                        class="flex-1 customer-button-primary text-sm px-3 py-2">
                    Order Now
                </button>
            </div>

            <!-- Save Date -->
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500">
                    <i data-lucide="heart" class="h-3 w-3 inline mr-1"></i>
                    Saved {{ $savedService->created_at ? $savedService->created_at->diffForHumans() : 'recently' }}
                </p>
            </div>
        </div>
    </div>
    @empty
    <!-- Empty State -->
    <div class="col-span-full">
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="heart" class="h-8 w-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No saved services yet</h3>
            <p class="text-gray-600 mb-6">Save your favorite printing services for quick access later</p>
            <a href="{{ route('customer.dashboard') }}" class="customer-button-primary">
                Browse Services
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($savedServices instanceof \Illuminate\Pagination\LengthAwarePaginator && $savedServices->hasPages())
<div class="mt-8 flex justify-center">
    <nav class="flex items-center gap-2">
        @if($savedServices->onFirstPage())
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $savedServices->previousPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">Previous</a>
        @endif

        @foreach($savedServices->getUrlRange(1, $savedServices->lastPage()) as $page => $url)
            @if($page == $savedServices->currentPage())
                <span class="px-3 py-2 bg-primary text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">{{ $page }}</a>
            @endif
        @endforeach

        @if($savedServices->hasMorePages())
            <a href="{{ $savedServices->nextPageUrl() }}" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors">Next</a>
        @else
            <span class="px-3 py-2 text-gray-400 cursor-not-allowed">Next</span>
        @endif
    </nav>
</div>
@endif
@endsection

@push('scripts')
<script>
// Selection management
function selectAll() {
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectionCount();
}

function selectNone() {
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectionCount();
}

function updateSelectionCount() {
    const selectedCount = document.querySelectorAll('.service-checkbox:checked').length;
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    if (selectedCount > 0) {
        addToCartBtn.disabled = false;
        addToCartBtn.textContent = `Add ${selectedCount} Selected to Cart`;
    } else {
        addToCartBtn.disabled = true;
        addToCartBtn.innerHTML = '<i data-lucide="shopping-cart" class="h-4 w-4 inline mr-2"></i>Add Selected to Cart';
    }
    
    lucide.createIcons();
}

// Service actions
function removeSavedService(serviceId) {
    if (confirm('Remove this service from your saved list?')) {
        CustomerLayout.showLoading();
        
        CustomerLayout.request('/saved-services/remove', {
            method: 'POST',
            body: JSON.stringify({ service_id: serviceId })
        })
            .then(data => {
                if (data.success) {
                    // Remove the card from DOM
                    document.querySelector(`[data-service-id="${serviceId}"]`).remove();
                    CustomerLayout.showNotification('Service removed from saved list');
                    CustomerLayout.updateSavedServicesBadge(data.count);
                    
                    // Check if no services left
                    if (document.querySelectorAll('[data-service-id]').length === 0) {
                        location.reload();
                    }
                } else {
                    CustomerLayout.showNotification(data.message || 'Failed to remove service', 'error');
                }
            })
            .catch(error => {
                CustomerLayout.showNotification('Failed to remove service', 'error');
            })
            .finally(() => {
                CustomerLayout.hideLoading();
            });
    }
}

function clearAllSaved() {
    if (confirm('Are you sure you want to remove all saved services? This action cannot be undone.')) {
        CustomerLayout.showLoading();
        
        CustomerLayout.request('/saved-services/clear', {
            method: 'POST'
        })
            .then(data => {
                if (data.success) {
                    CustomerLayout.showNotification('All saved services cleared');
                    CustomerLayout.updateSavedServicesBadge(0);
                    location.reload();
                } else {
                    CustomerLayout.showNotification(data.message || 'Failed to clear saved services', 'error');
                }
            })
            .catch(error => {
                CustomerLayout.showNotification('Failed to clear saved services', 'error');
            })
            .finally(() => {
                CustomerLayout.hideLoading();
            });
    }
}

function addSelectedToCart() {
    const selectedServices = Array.from(document.querySelectorAll('.service-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedServices.length === 0) {
        CustomerLayout.showNotification('Please select services to add to cart', 'error');
        return;
    }
    
    CustomerLayout.showLoading();
    
    CustomerLayout.request('/cart/add-multiple', {
        method: 'POST',
        body: JSON.stringify({ 
            service_ids: selectedServices,
            quantity: 1 
        })
    })
        .then(data => {
            if (data.success) {
                CustomerLayout.showNotification(`${selectedServices.length} services added to cart!`);
                setTimeout(() => {
                    window.location.href = '/cart';
                }, 1500);
            } else {
                CustomerLayout.showNotification(data.message || 'Failed to add services to cart', 'error');
            }
        })
        .catch(error => {
            CustomerLayout.showNotification('Failed to add services to cart', 'error');
        })
        .finally(() => {
            CustomerLayout.hideLoading();
        });
}

function viewServiceDetails(serviceId) {
    window.location.href = `/services/${serviceId}`;
}

function orderService(serviceId) {
    window.location.href = `/services/${serviceId}`;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectionCount();
});
</script>
@endpush
