@extends('layouts.public')

@section('title', 'Checkout')

@section('content')

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <i data-lucide="credit-card" class="h-8 w-8 text-primary"></i>
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
    </div>
    <p class="text-gray-600 text-lg">Complete your order and choose payment method</p>
</div>

<form id="checkout-form" class="space-y-8" data-up-global-loader data-up-loader-title="Placing your order…" data-up-loader-message="Processing checkout. Please wait.">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="user-circle" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="contact_name" name="contact_name" value="{{ $user->name ?? '' }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ $user->email ?? '' }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" placeholder="+63 9XX XXX XXXX" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                </div>
            </div>

            <!-- Rush Pickup Options -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="clock" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Pickup Timeline</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fulfillment *</label>
                        <div class="flex items-center gap-4">
                            @php
                                $canPickup = !isset($availableFulfillmentMethods) || in_array('pickup', $availableFulfillmentMethods);
                                $canDelivery = !isset($availableFulfillmentMethods) || in_array('delivery', $availableFulfillmentMethods);
                            @endphp
                            @if($canPickup)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="fulfillment_method" value="pickup" checked>
                                    <span>Pickup</span>
                                </label>
                            @endif
                            @if($canDelivery)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="fulfillment_method" value="delivery" {{ !$canPickup ? 'checked' : '' }}>
                                    <span>Delivery</span>
                                </label>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="requested_fulfillment_date" class="block text-sm font-medium text-gray-700 mb-2">Requested Date (Optional)</label>
                        <input type="date" id="requested_fulfillment_date" name="requested_fulfillment_date" min="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Order now, but choose a later pickup/delivery date.</p>
                    </div>
                </div>
                
                <div class="space-y-4" id="rush-options">
                    @php
                        $rushAllowed = !isset($supportsRushAll) || $supportsRushAll;
                        $rushOptions = $rushOptionsData ?? [
                            'standard' => ['enabled' => true, 'fee' => 0, 'lead_hours' => 48],
                            'express' => ['enabled' => false, 'fee' => 50, 'lead_hours' => 24],
                            'rush' => ['enabled' => false, 'fee' => 100, 'lead_hours' => 6],
                            'same_day' => ['enabled' => false, 'fee' => 200, 'lead_hours' => 3],
                        ];
                        
                        $rushMeta = [
                            'standard' => ['icon' => 'calendar', 'color' => 'blue', 'label' => 'Standard Pickup'],
                            'express' => ['icon' => 'zap', 'color' => 'orange', 'label' => 'Express Pickup'],
                            'rush' => ['icon' => 'flame', 'color' => 'red', 'label' => 'Rush Pickup', 'tag' => 'URGENT'],
                            'same_day' => ['icon' => 'rocket', 'color' => 'purple', 'label' => 'Same Day Pickup', 'tag' => 'PREMIUM'],
                        ];
                    @endphp

                    @if(!$rushAllowed)
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                            Rush pickup options are not available because one or more services in your order do not support rush.
                        </div>
                    @endif

                    @foreach($rushMeta as $key => $meta)
                        @php
                            $opt = $rushOptions[$key] ?? ['enabled' => false, 'fee' => 0, 'lead_hours' => 0];
                            $enabled = !empty($opt['enabled']) || $key === 'standard';
                            if (!$rushAllowed && $key !== 'standard') $enabled = false;
                            
                            $fee = (float) ($opt['fee'] ?? 0);
                            $leadHours = (int) ($opt['lead_hours'] ?? 0);
                            
                            // Human readable lead time
                            if ($leadHours >= 24) {
                                $days = round($leadHours / 24);
                                $timeDesc = "Ready in " . ($days == 1 ? "1 business day" : "$days business days");
                            } else {
                                $timeDesc = "Ready in $leadHours hours";
                            }
                        @endphp

                        @if($enabled || $key === 'standard')
                        <label class="relative cursor-pointer block">
                            <input type="radio" name="rush_option" value="{{ $key }}" {{ $key === 'standard' ? 'checked' : '' }} 
                                   class="sr-only peer" data-lead-hours="{{ $leadHours }}" data-fee="{{ $fee }}" {{ !$enabled ? 'disabled' : '' }}>
                            <div class="border-2 border-gray-200 rounded-lg p-4 transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 {{ !$enabled ? 'opacity-50 grayscale' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-{{ $meta['color'] }}-100 rounded-full flex items-center justify-center">
                                            <i data-lucide="{{ $meta['icon'] }}" class="h-5 w-5 text-{{ $meta['color'] }}-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 flex items-center gap-2">
                                                {{ $meta['label'] }}
                                                @if(isset($meta['tag']))
                                                    <span class="bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-800 text-xs font-medium px-2 py-1 rounded-full">{{ $meta['tag'] }}</span>
                                                @endif
                                            </h4>
                                            <p class="text-sm text-gray-600">{{ $timeDesc }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($fee > 0)
                                            <span class="text-lg font-bold text-{{ $meta['color'] }}-600">+₱{{ number_format($fee, 2) }}</span>
                                        @else
                                            <span class="text-lg font-bold text-green-600">FREE</span>
                                        @endif
                                        <p class="text-xs text-gray-500">Per order</p>
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                            </div>
                        </label>
                        @endif
                    @endforeach
                </div>

                <!-- Estimated Completion Time -->
                <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i data-lucide="clock" class="h-5 w-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-900">Estimated Completion</h4>
                            <p class="text-sm text-blue-700" id="estimated-completion">Your order will be ready by <span class="font-semibold">Friday, Nov 15, 2024 at 5:00 PM</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pickup Information -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="map-pin" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Pickup Information</h3>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="h-5 w-5 text-blue-600 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-blue-900 mb-1">Ready for Pickup</h4>
                            <p class="text-sm text-blue-700">Your order will be ready for pickup at the respective print shops. You will receive pickup instructions and shop contact details after placing your order.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="credit-card" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Payment Method</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="payment-methods">
                    <!-- GCash Payment -->
                    @php
                        $pm = $availablePaymentMethods ?? ['gcash', 'cash'];
                    @endphp
                    <x-ui.tooltip text="Online payments are not yet available. Please use Cash for now.">
                        <div class="relative opacity-60 cursor-not-allowed" aria-disabled="true">
                            <input type="radio" name="payment_method" value="gcash" class="sr-only" disabled>
                            <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="smartphone" class="h-6 w-6 text-blue-600"></i>
                                </div>
                                <div class="flex items-center justify-center gap-2 mb-1">
                                    <h4 class="font-semibold text-gray-900">GCash</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-secondary text-secondary-foreground rounded-full">Not yet available</span>
                                </div>
                                <p class="text-sm text-gray-600">Mobile wallet payment</p>
                                <p class="text-xs text-gray-500 mt-2">Coming soon</p>
                            </div>
                        </div>
                    </x-ui.tooltip>
                    
                    <!-- Cash Payment -->
                    @if(in_array('cash', $pm))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="cash" checked class="sr-only peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="banknote" class="h-6 w-6 text-green-600"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Cash</h4>
                            <p class="text-sm text-gray-600">Pay upon pickup</p>
                            <div class="absolute top-3 right-3 w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                            </div>
                        </div>
                    </label>
                    @endif

                    <x-ui.tooltip text="Online payments are not yet available. Please use Cash for now.">
                        <div class="relative opacity-60 cursor-not-allowed" aria-disabled="true">
                            <input type="radio" name="payment_method" value="paypal" class="sr-only" disabled>
                            <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="wallet" class="h-6 w-6 text-yellow-700"></i>
                                </div>
                                <div class="flex items-center justify-center gap-2 mb-1">
                                    <h4 class="font-semibold text-gray-900">PayPal</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-secondary text-secondary-foreground rounded-full">Not yet available</span>
                                </div>
                                <p class="text-sm text-gray-600">Card / PayPal balance</p>
                                <p class="text-xs text-gray-500 mt-2">Coming soon</p>
                            </div>
                        </div>
                    </x-ui.tooltip>
                </div>

                <div class="mt-4 text-xs text-muted-foreground">
                    Online payments are shown for visibility, but are not yet available.
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="message-square" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Additional Notes</h3>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any special requirements or notes for this order..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-card border border-border rounded-xl shadow-card p-6 sticky top-6">
                <div class="flex items-center gap-2 mb-6">
                    <i data-lucide="receipt" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Order Summary</h3>
                </div>
                
                <!-- Cart Items -->
                <div class="space-y-4 mb-6">
                    @foreach($cartItems as $item)
                    <div class="flex justify-between items-start pb-4 border-b border-border last:border-b-0">
                        <div class="flex-1">
                            @php
                                $service = $item['service'] ?? ($item['product'] ?? null);
                            @endphp
                            <h4 class="font-medium text-gray-900 mb-1">{{ $service->service_name ?? 'Unknown Service' }}</h4>
                            <p class="text-sm text-gray-600 mb-1">{{ $service->enterprise_name ?? '' }}</p>
                            <p class="text-sm text-gray-500">
                                Qty: {{ $item['quantity'] }} × ₱{{ number_format($item['unit_price'], 2) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-gray-900">₱{{ number_format($item['total'], 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium" id="subtotal">₱{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm" id="rush-fee-row" style="display: none;">
                        <span class="text-gray-600">Rush Fee:</span>
                        <span class="font-medium text-orange-600" id="rush-fee">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (12%):</span>
                        <span class="font-medium" id="tax">₱{{ number_format($tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-border">
                        <span>Total:</span>
                        <span class="text-primary" id="total">₱{{ number_format($total, 2) }}</span>
                    </div>
                </div>
                
                <!-- Place Order Button -->
                <button type="submit" id="place-order-btn" data-up-button-loader
                        class="w-full bg-primary text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary/90 transition-colors flex flex-col items-center justify-center gap-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="credit-card" class="h-5 w-5"></i>
                        <span>Order Now</span>
                    </div>
                    <div class="text-xs opacity-90" id="order-completion-time">
                        Ready by Friday, Nov 15, 2024 at 5:00 PM
                    </div>
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
const subtotal = {{ $subtotal }};
const taxRate = 0.12;
let currentRushFee = 0;

// Calculate completion time based on rush option
function calculateCompletionTime(rushType) {
    const now = new Date();
    const radio = document.querySelector(`input[name="rush_option"][value="${rushType}"]`);
    if (!radio) return now;
    
    const leadHours = parseFloat(radio.dataset.leadHours || 0);
    let completionTime = new Date(now);
    
    if (leadHours <= 6) {
        // Short lead times: just add hours
        completionTime.setHours(now.getHours() + leadHours);
    } else {
        // Longer lead times: respect business hours (approx 9 AM - 5 PM)
        let hoursRemaining = leadHours;
        while (hoursRemaining > 0) {
            completionTime.setHours(completionTime.getHours() + 1);
            
            // Skip weekends (optional, but professional)
            const day = completionTime.getDay();
            if (day === 0 || day === 6) continue;
            
            // Only count "business hours" (9 AM to 5 PM)
            const hour = completionTime.getHours();
            if (hour >= 9 && hour < 17) {
                hoursRemaining--;
            }
        }
    }
    
    return completionTime;
}

// Format completion time for display
function formatCompletionTime(date) {
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };
    
    return date.toLocaleDateString('en-US', options);
}

// Update pricing and completion time
function updateOrderSummary(rushType) {
    const radio = document.querySelector(`input[name="rush_option"][value="${rushType}"]`);
    if (!radio) return;
    
    const fee = parseFloat(radio.dataset.fee || 0);
    const leadHours = parseFloat(radio.dataset.leadHours || 0);
    
    // Update rush fee
    currentRushFee = fee;
    const rushFeeRow = document.getElementById('rush-fee-row');
    const rushFeeAmount = document.getElementById('rush-fee');
    
    if (currentRushFee > 0) {
        rushFeeRow.style.display = 'flex';
        rushFeeAmount.textContent = `₱${currentRushFee.toFixed(2)}`;
        
        // Update rush fee color based on urgency
        rushFeeAmount.className = 'font-medium';
        if (rushType === 'express') {
            rushFeeAmount.classList.add('text-orange-600');
        } else if (rushType === 'rush') {
            rushFeeAmount.classList.add('text-red-600');
        } else if (rushType === 'same_day') {
            rushFeeAmount.classList.add('text-purple-600');
        }
    } else {
        rushFeeRow.style.display = 'none';
    }
    
    // Calculate new total
    const newSubtotal = subtotal + currentRushFee;
    const newTax = newSubtotal * taxRate;
    const newTotal = newSubtotal + newTax;
    
    // Update display
    document.getElementById('tax').textContent = `₱${newTax.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${newTotal.toFixed(2)}`;
    
    // Update completion time
    const completionTime = calculateCompletionTime(rushType);
    const formattedTime = formatCompletionTime(completionTime);
    
    // Update estimated completion in pickup options
    const estimatedCompletionEl = document.getElementById('estimated-completion');
    if (estimatedCompletionEl) {
        estimatedCompletionEl.innerHTML = `Your order will be ready by <span class="font-semibold">${formattedTime}</span>`;
    }
    
    // Update order button completion time
    const orderCompletionTimeEl = document.getElementById('order-completion-time');
    if (orderCompletionTimeEl) {
        orderCompletionTimeEl.textContent = `Ready by ${formattedTime}`;
    }
    
    // Add visual feedback for urgent orders
    const orderBtn = document.getElementById('place-order-btn');
    if (orderBtn) {
        orderBtn.className = 'w-full text-white py-3 px-6 rounded-lg font-semibold transition-colors flex flex-col items-center justify-center gap-1';
        
        if (rushType === 'rush' || rushType === 'same_day') {
            orderBtn.classList.add('bg-gradient-to-r', 'from-red-500', 'to-red-600', 'hover:from-red-600', 'hover:to-red-700', 'shadow-lg');
            if (rushType === 'same_day') {
                orderBtn.classList.remove('from-red-500', 'to-red-600', 'hover:from-red-600', 'hover:to-red-700');
                orderBtn.classList.add('from-purple-500', 'to-purple-600', 'hover:from-purple-600', 'hover:to-purple-700');
            }
        } else if (rushType === 'express') {
            orderBtn.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'hover:from-orange-600', 'hover:to-orange-700');
        } else {
            orderBtn.classList.add('bg-primary', 'hover:bg-primary/90');
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Rush option listeners
    const rushRadios = document.querySelectorAll('input[name="rush_option"]');
    rushRadios.forEach((radio) => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                updateOrderSummary(this.value);
            }
        });
    });

    // Initialize with default selection
    const defaultOption = document.querySelector('input[name="rush_option"]:checked');
    if (defaultOption) {
        updateOrderSummary(defaultOption.value);
    }

    // Form Submission
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const btn = document.getElementById('place-order-btn');
            const originalText = btn ? btn.innerHTML : '';
            
            // Show global loader immediately
            if (window.UniPrintUI && typeof window.UniPrintUI.loading === 'object' && window.UniPrintUI.loading.show) {
                window.UniPrintUI.loading.show({
                    title: 'Placing your order...',
                    message: 'Processing checkout. Please wait.'
                });
            } else if (window.UniPrintUI && typeof window.UniPrintUI.showLoading === 'function') {
                window.UniPrintUI.showLoading({
                    title: 'Placing your order...',
                    message: 'Processing checkout. Please wait.'
                });
            }
            
            if (btn) {
                if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
                    UniPrintUI.setButtonLoading(btn, true, { text: 'Processing...' });
                } else {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="up-inline-spinner" aria-hidden="true"></span><span>Processing...</span>';
                }
            }

            try {
                const formData = new FormData(this);
                const response = await fetch('{{ route("checkout.process") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Hide loader
                    if (window.UniPrintUI && typeof window.UniPrintUI.loading === 'object' && window.UniPrintUI.loading.hide) {
                        window.UniPrintUI.loading.hide();
                    } else if (window.UniPrintUI && typeof window.UniPrintUI.hideLoading === 'function') {
                        window.UniPrintUI.hideLoading();
                    }

                    let message = 'Order placed successfully! ';
                    message += 'You will receive pickup instructions shortly.';

                    if (window.UniPrintUI && typeof window.UniPrintUI.alert === 'function') {
                        await window.UniPrintUI.alert(message, { title: 'Success', variant: 'success' });
                    } else {
                        alert(message);
                    }
                    
                    window.location.href = '{{ route("customer.orders") }}';
                    return;
                }

                throw new Error(result.message || 'Failed to place order');
            } catch (error) {
                console.error('Checkout error:', error);
                
                // Hide loader on error
                if (window.UniPrintUI && typeof window.UniPrintUI.loading === 'object' && window.UniPrintUI.loading.hide) {
                    window.UniPrintUI.loading.hide();
                } else if (window.UniPrintUI && typeof window.UniPrintUI.hideLoading === 'function') {
                    window.UniPrintUI.hideLoading();
                }
                
                if (window.UniPrintUI && typeof window.UniPrintUI.alert === 'function') {
                    window.UniPrintUI.alert(error.message || 'Checkout failed', { title: 'Error', variant: 'danger' });
                } else {
                    alert('Error: ' + (error.message || 'Checkout failed'));
                }

                if (btn) {
                    if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
                        UniPrintUI.setButtonLoading(btn, false);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                }
                
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        });
    }

    // Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
