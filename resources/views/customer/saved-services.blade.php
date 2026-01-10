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
                    @foreach($savedServices as $item)
                        <div class="bg-card border border-border rounded-xl shadow-card p-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 pt-1">
                                    <input type="checkbox" class="saved-service-select h-4 w-4 text-primary rounded" value="{{ $item->saved_service_id }}">
                                </div>
                                <div class="w-24 h-24 bg-secondary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="printer" class="h-12 w-12 text-primary"></i>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-bold text-lg">{{ $item->service->service_name ?? 'Unknown Service' }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ $item->service->enterprise->name ?? 'Unknown Shop' }}</p>
                                            @if($item->special_instructions)
                                                <p class="text-xs text-muted-foreground mt-1">
                                                    <i data-lucide="message-square" class="h-3 w-3 inline mr-1"></i>
                                                    {{ $item->special_instructions }}
                                                </p>
                                            @endif
                                        </div>
                                        <form action="{{ route('saved-services.remove', $item->saved_service_id) }}" method="POST" class="remove-item-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-destructive hover:text-destructive/80 p-1">
                                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                                            </button>
                                        </form>
                                    </div>

                                    @if($item->customizationOptions->isNotEmpty())
                                        <div class="mb-3">
                                            <p class="text-sm font-medium mb-1">Service Options:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($item->customizationOptions->groupBy('option_type') as $type => $options)
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
                                                    <button type="button" onclick="updateQuantity('{{ $item->saved_service_id }}', {{ $item->quantity - 1 }})" 
                                                            class="px-2 py-1 hover:bg-secondary transition-smooth">-</button>
                                                    <input type="number" value="{{ $item->quantity }}" min="1" max="100" 
                                                           class="w-16 px-2 py-1 text-center border-0 focus:outline-none quantity-input"
                                                           data-item-id="{{ $item->saved_service_id }}"
                                                           onchange="updateQuantity('{{ $item->saved_service_id }}', this.value)">
                                                    <button type="button" onclick="updateQuantity('{{ $item->saved_service_id }}', {{ $item->quantity + 1 }})" 
                                                            class="px-2 py-1 hover:bg-secondary transition-smooth">+</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-sm text-muted-foreground">₱{{ number_format($item->unit_price, 2) }} each</p>
                                            <p class="text-lg font-bold text-primary">₱{{ number_format($item->total_price, 2) }}</p>
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
                                <span class="text-muted-foreground">Subtotal ({{ $savedServices->count() }} items)</span>
                                <span class="font-medium">₱{{ number_format($savedServices->sum('total_price'), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Estimated Tax</span>
                                <span class="font-medium">₱0.00</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between text-lg font-bold mb-6">
                            <span>Total</span>
                            <span class="text-primary">₱{{ number_format($savedServices->sum('total_price'), 2) }}</span>
                        </div>

                        <button type="button" onclick="checkoutSelected()" class="block w-full text-center px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth mb-3">
                            Proceed to Checkout (Selected)
                        </button>

                        <a href="{{ route('checkout.index') }}" class="block w-full text-center px-6 py-3 border border-input rounded-lg hover:bg-secondary transition-smooth mb-3">
                            Proceed to Checkout (All)
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
async function checkoutSelected() {
    const selected = Array.from(document.querySelectorAll('.saved-service-select:checked')).map(cb => cb.value);

    if (!selected.length) {
        if (window.UniPrintUI?.alert) {
            await window.UniPrintUI.alert('Please select at least one service to checkout.', { title: 'No Selection', variant: 'warning' });
        } else {
            alert('Please select at least one service to checkout.');
        }
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) throw new Error('CSRF token not found');

        const res = await fetch(`/saved-services/selection`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ selected })
        });

        if (!res.ok) {
            let message = `Failed to set selection (HTTP ${res.status})`;
            try {
                const ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const data = await res.json();
                    message = data?.message || message;
                } else {
                    const text = await res.text();
                    if (text) message = text;
                }
            } catch (e) {}
            throw new Error(message);
        }

        const data = await res.json().catch(() => null);
        if (!data || !data.success) {
            throw new Error(data?.message || 'Failed to set selection');
        }

        window.location.href = '{{ route("checkout.index") }}';
    } catch (e) {
        console.error('checkoutSelected error', e);
        const msg = e?.message || 'Failed to proceed to checkout.';
        if (window.UniPrintUI?.alert) {
            await window.UniPrintUI.alert(msg, { title: 'Error', variant: 'danger' });
        } else {
            alert(msg);
        }
    }
}

async function updateQuantity(serviceId, quantity) {
    if (quantity < 1) {
        const ok = window.UniPrintUI?.confirm
            ? await window.UniPrintUI.confirm('Remove this service from saved services?', { title: 'Remove Service', confirmText: 'Remove', cancelText: 'Cancel', danger: true })
            : confirm('Remove this service from saved services?');

        if (ok) {
            removeService(serviceId);
        }
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert('Security token missing. Please refresh the page.', { title: 'Error', variant: 'danger' });
            } else {
                alert('Security token missing. Please refresh the page.');
            }
            return;
        }

        const response = await fetch(`/saved-services/${serviceId}`, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        });

        // Check if response is ok
        if (!response.ok) {
            console.error('Response status:', response.status);
            if (response.status === 401) {
                if (window.UniPrintUI?.alert) {
                    await window.UniPrintUI.alert('Your session has expired. Please login again.', { title: 'Session Expired', variant: 'warning' });
                } else {
                    alert('Your session has expired. Please login again.');
                }
                window.location.href = '{{ route("login") }}';
                return;
            }

            let message = `HTTP error! status: ${response.status}`;
            try {
                const ct = response.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const err = await response.json();
                    message = err?.message || message;
                } else {
                    const text = await response.text();
                    if (text) message = text;
                }
            } catch (e) {}
            throw new Error(message);
        }

        // Check if response has content
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Invalid response content type:', contentType);
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert('Server returned an invalid response. Please try again.', { title: 'Error', variant: 'danger' });
            } else {
                alert('Server returned an invalid response. Please try again.');
            }
            return;
        }

        const result = await response.json();

        if (result.success) {
            if (result.removed) {
                window.location.reload();
                return;
            }
            window.location.reload();
        } else {
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert(result.message || 'Failed to update quantity', { title: 'Error', variant: 'danger' });
            } else {
                alert(result.message || 'Failed to update quantity');
            }
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        const msg = error?.message || 'Network error. Please check your connection and try again.';
        if (window.UniPrintUI?.alert) {
            await window.UniPrintUI.alert(msg, { title: 'Network Error', variant: 'danger' });
        } else {
            alert(msg);
        }
    }
}

async function removeService(serviceId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert('Security token missing. Please refresh the page.', { title: 'Error', variant: 'danger' });
            } else {
                alert('Security token missing. Please refresh the page.');
            }
            return;
        }

        const response = await fetch(`/saved-services/${serviceId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        // Check if response is ok
        if (!response.ok) {
            console.error('Response status:', response.status);
            if (response.status === 401) {
                if (window.UniPrintUI?.alert) {
                    await window.UniPrintUI.alert('Your session has expired. Please login again.', { title: 'Session Expired', variant: 'warning' });
                } else {
                    alert('Your session has expired. Please login again.');
                }
                window.location.href = '{{ route("login") }}';
                return;
            }

            let message = `HTTP error! status: ${response.status}`;
            try {
                const ct = response.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const err = await response.json();
                    message = err?.message || message;
                } else {
                    const text = await response.text();
                    if (text) message = text;
                }
            } catch (e) {}

            throw new Error(message);
        }

        // Check if response has content
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Invalid response content type:', contentType);
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert('Server returned an invalid response. Please try again.', { title: 'Error', variant: 'danger' });
            } else {
                alert('Server returned an invalid response. Please try again.');
            }
            return;
        }

        const result = await response.json();

        if (result.success) {
            window.location.reload();
        } else {
            if (window.UniPrintUI?.alert) {
                await window.UniPrintUI.alert(result.message || 'Failed to remove item', { title: 'Error', variant: 'danger' });
            } else {
                alert(result.message || 'Failed to remove item');
            }
        }
    } catch (error) {
        console.error('Error removing item:', error);
        const msg = error?.message || 'Network error. Please check your connection and try again.';
        if (window.UniPrintUI?.alert) {
            await window.UniPrintUI.alert(msg, { title: 'Network Error', variant: 'danger' });
        } else {
            alert(msg);
        }
    }
}

// Handle remove item forms
document.querySelectorAll('.remove-item-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const ok = window.UniPrintUI?.confirm
            ? await window.UniPrintUI.confirm('Remove this item from saved services?', { title: 'Remove Service', confirmText: 'Remove', cancelText: 'Cancel', danger: true })
            : confirm('Remove this item from saved services?');

        if (ok) {
            const itemId = this.action.split('/').pop();
            removeService(itemId);
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
