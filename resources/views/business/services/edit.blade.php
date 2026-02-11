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
        <form action="{{ route('business.services.update', $service->service_id) }}" method="POST" enctype="multipart/form-data" data-up-global-loader>
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Service Name *</label>
                    <input type="text" name="service_name" value="{{ old('service_name', $service->service_name) }}" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Service Images</label>
                    @if(isset($serviceImages) && $serviceImages->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
                            @foreach($serviceImages as $img)
                                <div class="relative">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="Service image" class="w-full h-24 object-cover rounded-lg border border-border" />
                                    @if(!empty($img->is_primary))
                                        <span class="absolute top-2 left-2 text-[10px] bg-primary text-primary-foreground px-2 py-1 rounded-md">Primary</span>
                                    @else
                                        <button type="button"
                                                class="absolute top-2 left-2 text-[10px] bg-secondary text-secondary-foreground px-2 py-1 rounded-md hover:opacity-90"
                                                onclick="setServicePrimaryImage('{{ $service->service_id }}', '{{ $img->image_id }}')">
                                            Set Primary
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty($service->image_path))
                        <div class="mb-3">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($service->image_path) }}" alt="Service image" class="w-full max-w-md rounded-lg border border-border" />
                        </div>
                    @endif
                    <input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring" />
                    <div class="mt-2 text-xs text-muted-foreground">You can select multiple images at once to add to this service.</div>
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
                        <x-ui.form.checkbox
                            name="allowed_payment_methods[]"
                            id="pm_paypal"
                            value="paypal"
                            :checked="in_array('paypal', old('allowed_payment_methods', $allowedPaymentMethods))"
                            label="PayPal"
                        />
                    </div>
                </div>

                <x-ui.form.switch
                    name="is_active"
                    id="is_active"
                    :checked="old('is_active', $service->is_active)"
                    label="Active"
                />

                @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'file_upload_enabled'))
                    <x-ui.form.switch
                        name="file_upload_enabled"
                        id="file_upload_enabled"
                        :checked="old('file_upload_enabled', !empty($service->file_upload_enabled))"
                        label="Enable File Upload"
                    />
                @endif

                @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload'))
                    <x-ui.form.switch
                        name="requires_file_upload"
                        id="requires_file_upload"
                        :checked="old('requires_file_upload', !empty($service->requires_file_upload))"
                        label="Require File Upload"
                    />
                @endif

                @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_downpayment') && \Illuminate\Support\Facades\Schema::hasColumn('services', 'downpayment_percent'))
                    <x-ui.form.switch
                        name="requires_downpayment"
                        id="requires_downpayment"
                        :checked="old('requires_downpayment', !empty($service->requires_downpayment))"
                        label="Require Downpayment"
                    />

                    <div>
                        <label class="block text-sm font-medium mb-2">Downpayment Percent (%)</label>
                        <input type="number" name="downpayment_percent" value="{{ old('downpayment_percent', $service->downpayment_percent ?? 0) }}" step="0.01" min="0" max="100"
                               class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                @endif

                @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'supports_rush'))
                    <x-ui.form.switch
                        name="supports_rush"
                        id="supports_rush"
                        :checked="old('supports_rush', !empty($service->supports_rush))"
                        label="Supports Rush"
                    />
                @endif

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('business.services.index') }}" 
                       class="flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth" data-up-button-loader>
                        Update Service
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
async function setServicePrimaryImage(serviceId, imageId) {
    try {
        const url = `{{ route('business.services.images.primary', [':serviceId', ':imageId']) }}`
            .replace(':serviceId', serviceId)
            .replace(':imageId', imageId);

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || data.success !== true) {
            throw new Error((data && data.message) ? data.message : 'Failed to set primary image');
        }

        if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
            window.UniPrintUI.toast(data.message || 'Updated.', { variant: 'success' });
        }

        window.location.reload();
    } catch (err) {
        if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
            window.UniPrintUI.toast(err.message || 'Failed to set primary image.', { variant: 'danger' });
        }
    }
}
</script>
@endsection
