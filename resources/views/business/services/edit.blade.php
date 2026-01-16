@extends('layouts.business')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')
@section('page-subtitle', 'Update service information')

@section('header-actions')
<a href="{{ route('business.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Services
</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        @php
            $allowedPaymentMethods = [];
            if (!empty($service->allowed_payment_methods)) {
                $decoded = json_decode($service->allowed_payment_methods, true);
                if (is_array($decoded)) {
                    $allowedPaymentMethods = $decoded;
                }
            }
            if (empty($allowedPaymentMethods)) {
                $allowedPaymentMethods = ['gcash', 'cash'];
            }
        @endphp
        <form action="{{ route('business.services.update', $service->service_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Service Name *</label>
                    <input type="text" name="service_name" value="{{ old('service_name', $service->service_name) }}" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Service Image</label>
                    @if(!empty($service->image_path))
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="Service image" class="w-full max-w-md rounded-lg border border-border" />
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('description', $service->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Base Price (₱) *</label>
                    <input type="number" name="base_price" value="{{ old('base_price', $service->base_price) }}" step="0.01" min="0" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Fulfillment Type *</label>
                    <select name="fulfillment_type" required class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="pickup" {{ old('fulfillment_type', $service->fulfillment_type ?? 'pickup') === 'pickup' ? 'selected' : '' }}>Pickup</option>
                        <option value="delivery" {{ old('fulfillment_type', $service->fulfillment_type ?? '') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        <option value="both" {{ old('fulfillment_type', $service->fulfillment_type ?? '') === 'both' ? 'selected' : '' }}>Pickup & Delivery</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Allowed Payment Methods</label>
                    <div class="flex items-center gap-4">
                        <x-ui.form.checkbox
                            name="allowed_payment_methods[]"
                            id="pm_gcash"
                            value="gcash"
                            :checked="in_array('gcash', old('allowed_payment_methods', $allowedPaymentMethods))"
                            label="GCash"
                        />
                        <x-ui.form.checkbox
                            name="allowed_payment_methods[]"
                            id="pm_cash"
                            value="cash"
                            :checked="in_array('cash', old('allowed_payment_methods', $allowedPaymentMethods))"
                            label="Cash"
                        />
                    </div>
                </div>

                <x-ui.form.checkbox
                    name="is_active"
                    id="is_active"
                    :checked="old('is_active', $service->is_active)"
                    label="Active"
                />

                @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload'))
                    <x-ui.form.checkbox
                        name="requires_file_upload"
                        id="requires_file_upload"
                        :checked="old('requires_file_upload', !empty($service->requires_file_upload))"
                        label="Requires File Upload"
                    />
                @endif

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('business.services.index') }}" 
                       class="flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                        Update Service
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
