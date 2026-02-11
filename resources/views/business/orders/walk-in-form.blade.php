@php
    $isWalkIn = isset($services);
@endphp

<div class="p-6">
    <form action="{{ route('business.orders.walk-in.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Customer Info -->
            <div class="space-y-4">
                <h2 class="text-lg font-bold border-b border-border pb-2">Customer Details</h2>
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name *</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" 
                           class="w-full px-3 py-2 border border-input rounded-lg bg-background" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" 
                               class="w-full px-3 py-2 border border-input rounded-lg bg-background">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}" 
                               class="w-full px-3 py-2 border border-input rounded-lg bg-background">
                    </div>
                </div>
            </div>

            <!-- Order Info -->
            <div class="space-y-4">
                <h2 class="text-lg font-bold border-b border-border pb-2">Order Information</h2>
                <div>
                    <label class="block text-sm font-medium mb-1">Purpose/Project Name *</label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}" 
                           placeholder="e.g. Marketing Flyers"
                           class="w-full px-3 py-2 border border-input rounded-lg bg-background" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Fulfillment</label>
                        <select name="fulfillment_method" class="w-full px-3 py-2 border border-input rounded-lg bg-background" required>
                            <option value="pickup" {{ old('fulfillment_method') === 'pickup' ? 'selected' : '' }}>Pickup</option>
                            <option value="delivery" {{ old('fulfillment_method') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Target Date</label>
                        <input type="date" name="requested_fulfillment_date" value="{{ old('requested_fulfillment_date') }}" 
                               class="w-full px-3 py-2 border border-input rounded-lg bg-background">
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Selection -->
        <div class="border-t border-border pt-6 space-y-4">
            <h2 class="text-lg font-bold">Item Details</h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium mb-1">Service *</label>
                    <select name="service_id" class="w-full px-3 py-2 border border-input rounded-lg bg-background" required>
                        <option value="" disabled selected>Select a service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->service_id }}" {{ old('service_id') === $service->service_id ? 'selected' : '' }}>
                                {{ $service->service_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 1) }}" 
                           class="w-full px-3 py-2 border border-input rounded-lg bg-background" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Custom Unit Price</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-muted-foreground">₱</span>
                        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price') }}" 
                               placeholder="Leave blank for base price"
                               class="w-full pl-8 pr-3 py-2 border border-input rounded-lg bg-background" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
            <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                Create Walk-in Order
            </button>
        </div>
    </form>
</div>
