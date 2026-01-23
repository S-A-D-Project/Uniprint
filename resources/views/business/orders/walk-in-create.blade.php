@extends('layouts.business')

@section('title', 'Create Walk-in Order')
@section('page-title', 'Create Walk-in Order')
@section('page-subtitle', 'Manually add an order for walk-in customers')

@section('header-actions')
    <a href="{{ route('business.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Back to Orders
    </a>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div class="bg-card border border-border rounded-xl shadow-card p-6">
            <form action="{{ route('business.orders.walk-in.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <h2 class="text-lg font-bold">Customer</h2>
                    <p class="text-sm text-muted-foreground">Use contact details for walk-in customer reference.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Name</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium">Email (optional)</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg">
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <h2 class="text-lg font-bold mb-2">Order</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium">Purpose</label>
                            <input type="text" name="purpose" value="{{ old('purpose') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" required>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium">Fulfillment Method</label>
                                <select name="fulfillment_method" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" required>
                                    <option value="pickup" {{ old('fulfillment_method', 'pickup') === 'pickup' ? 'selected' : '' }}>Pickup</option>
                                    <option value="delivery" {{ old('fulfillment_method') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium">Requested Fulfillment Date</label>
                                <input type="date" name="requested_fulfillment_date" value="{{ old('requested_fulfillment_date') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Rush Option (optional)</label>
                            <input type="text" name="rush_option" value="{{ old('rush_option') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" placeholder="standard">
                        </div>
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <h2 class="text-lg font-bold mb-2">Item</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium">Service</label>
                            <select name="service_id" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" required>
                                <option value="" disabled {{ old('service_id') ? '' : 'selected' }}>Select a service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->service_id }}" {{ old('service_id') === $service->service_id ? 'selected' : '' }}>
                                        {{ $service->service_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', 1) }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" min="1" required>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Unit Price (optional)</label>
                            <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price') }}" class="mt-1 w-full px-3 py-2 border border-input rounded-lg" min="0">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('business.orders.index') }}" class="px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth">
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
