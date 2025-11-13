@extends('layouts.public')

@section('title', 'Saved Services - UniPrint')

@section('content')
<div class="min-h-screen bg-background py-12">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2 flex items-center gap-3">
                <i data-lucide="heart" class="h-8 w-8 text-primary"></i>
                Saved Services
            </h1>
            <p class="text-muted-foreground">Manage your saved printing services and proceed to checkout</p>
        </div>

        @if($savedServices->isEmpty())
            <div class="bg-card border border-border rounded-xl shadow-card p-12 text-center">
                <i data-lucide="heart" class="h-24 w-24 mx-auto mb-4 text-muted-foreground"></i>
                <h2 class="text-2xl font-bold mb-2">No saved services yet</h2>
                <p class="text-muted-foreground mb-6">Start browsing printing services to save items here</p>
                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    Browse Services
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        @else
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Saved Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($services as $service)
                        <div class="bg-card border border-border rounded-xl shadow-card p-6">
                            <div class="flex gap-4">
                                <div class="w-24 h-24 bg-secondary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="printer" class="h-12 w-12 text-primary"></i>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-bold text-lg">{{ $service->product->product_name ?? 'Unknown Product' }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ $service->product->enterprise->name ?? 'Unknown Shop' }}</p>
                                            @if($service->special_instructions)
                                                <p class="text-xs text-muted-foreground mt-1">
                                                    <i data-lucide="message-square" class="h-3 w-3 inline mr-1"></i>
                                                    {{ $service->special_instructions }}
                                                </p>
                                            @endif
                                        </div>
                                        <form action="{{ route('saved-services.remove', $service->saved_service_id) }}" method="POST" class="remove-service-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-destructive hover:text-destructive/80 p-1">
                                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                                            </button>
                                        </form>
                                    </div>

                                    @if($service->customizationOptions->isNotEmpty())
                                        <div class="mb-3">
                                            <p class="text-sm font-medium mb-1">Service Options:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($service->customizationOptions->groupBy('option_type') as $type => $options)
                                                    <span class="inline-block px-2 py-1 text-xs bg-primary/10 text-primary rounded-md">
                                                        {{ $type }}: {{ $options->pluck('option_name')->join(', ') }}
                                                        @if($options->sum('price_modifier') > 0)
                                                            (+₱{{ number_format($options->sum('price_modifier'), 2) }})
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-2">
                                                <label class="text-sm font-medium">Qty:</label>
                                                <div class="flex items-center border border-input rounded-md">
                                                    <button type="button" onclick="updateQuantity('{{ $service->saved_service_id }}', {{ $service->quantity - 1 }})" 
                                                            class="px-2 py-1 hover:bg-secondary transition-smooth">-</button>
                                                    <input type="number" value="{{ $service->quantity }}" min="1" max="100" 
                                                           class="w-16 px-2 py-1 text-center border-0 focus:outline-none quantity-input"
                                                           data-service-id="{{ $service->saved_service_id }}"
                                                           onchange="updateQuantity('{{ $service->saved_service_id }}', this.value)">
                                                    <button type="button" onclick="updateQuantity('{{ $service->saved_service_id }}', {{ $service->quantity + 1 }})" 
                                                            class="px-2 py-1 hover:bg-secondary transition-smooth">+</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-sm text-muted-foreground">₱{{ number_format($service->unit_price, 2) }} each</p>
                                            <p class="text-lg font-bold text-primary">{{ $service->formatted_total_price }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-card border border-border rounded-xl shadow-card p-6 sticky top-20">
                        <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-4 pb-4 border-b border-border">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal ({{ $savedServices->total_items }} items)</span>
                                <span class="font-medium">₱{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Estimated Tax</span>
                                <span class="font-medium">₱0.00</span>
                            </div>
                            @if($shipping > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Shipping</span>
                                <span class="font-medium">₱{{ number_format($shipping, 2) }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold mb-6">
                            <span>Total</span>
                            <span class="text-primary">₱{{ number_format($total, 2) }}</span>
                        </div>
                        
                        <a href="{{ route('checkout.index') }}" class="block w-full text-center px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth mb-3">
                            Proceed to Checkout
                        </a>
                        
                        <a href="{{ route('enterprises.index') }}" class="block w-full text-center px-6 py-3 border border-input rounded-lg hover:bg-secondary transition-smooth">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Saved Services functionality
async function updateQuantity(serviceId, quantity) {
    if (quantity < 1) {
        if (confirm('Remove this service from saved items?')) {
            removeService(serviceId);
        }
        return;
    }

    try {
        const response = await fetch(`/saved-services/${serviceId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ quantity: quantity })
        });

        const result = await response.json();

        if (result.success) {
            // Update the displayed total price
            const input = document.querySelector(`input[data-service-id="${serviceId}"]`);
            const priceDisplay = input.closest('.flex').querySelector('.text-primary');
            priceDisplay.textContent = result.total_price;
            
            // Update order summary
            updateOrderSummary();
        } else {
            alert(result.message || 'Failed to update quantity');
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert('Network error. Please try again.');
    }
}

async function removeService(serviceId) {
    try {
        const response = await fetch(`/saved-services/${serviceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        if (result.success) {
            // Remove the service card
            const serviceCard = document.querySelector(`form[action*="${serviceId}"]`).closest('.bg-card');
            serviceCard.remove();
            
            // Update header badge
            updateHeaderBadge(result.count);
            
            // Check if empty
            if (result.count === 0) {
                location.reload();
            } else {
                updateOrderSummary();
            }
        } else {
            alert(result.message || 'Failed to remove service');
        }
    } catch (error) {
        console.error('Error removing service:', error);
        alert('Network error. Please try again.');
    }
}

function updateOrderSummary() {
    // Reload to get updated totals
    location.reload();
}

function updateHeaderBadge(count) {
    const badge = document.querySelector('.saved-services-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Handle remove service forms
document.querySelectorAll('.remove-service-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm('Remove this service from saved items?')) {
            const serviceId = this.action.split('/').pop();
            removeService(serviceId);
        }
    });
});

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection
