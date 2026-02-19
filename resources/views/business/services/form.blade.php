@extends('layouts.business')

@section('title', isset($service) ? 'Edit Service' : 'Create Service')
@section('page-title', isset($service) ? 'Edit Service' : 'Create New Service')
@section('page-subtitle', isset($service) ? 'Update service information' : 'Add a new service to your catalog')

@section('header-actions')
<a href="{{ route('business.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Services
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        @php
            $isEdit = isset($service);
            $formAction = $isEdit ? route('business.services.update', $service->service_id) : route('business.services.store');
            
            $allowedPaymentMethods = [];
            if ($isEdit && !empty($service->allowed_payment_methods)) {
                $decoded = json_decode($service->allowed_payment_methods, true);
                if (is_array($decoded)) {
                    $allowedPaymentMethods = $decoded;
                }
            }
            if (empty($allowedPaymentMethods)) {
                $allowedPaymentMethods = ['gcash', 'cash'];
            }
        @endphp

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" data-up-global-loader class="needs-validation" novalidate>
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="space-y-6">
                <!-- Service Name & Base Price -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label for="service_name" class="block text-sm font-medium mb-2">Service Name *</label>
                        <input type="text" id="service_name" name="service_name" 
                               value="{{ old('service_name', $service->service_name ?? '') }}" 
                               placeholder="Enter service name" required
                               class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('service_name') border-destructive @enderror">
                        @error('service_name')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="base_price" class="block text-sm font-medium mb-2">Base Price (₱) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-muted-foreground">₱</span>
                            <input type="number" id="base_price" name="base_price" 
                                   value="{{ old('base_price', $service->base_price ?? '') }}" 
                                   step="0.01" min="0" placeholder="0.00" required
                                   class="w-full pl-8 pr-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('base_price') border-destructive @enderror">
                        </div>
                        @error('base_price')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe your service features..."
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('description', $service->description ?? '') }}</textarea>
                    @error('description')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Images -->
                <div>
                    <label class="block text-sm font-medium mb-2">Service Images</label>
                    @if($isEdit)
                        @if(isset($serviceImages) && $serviceImages->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                @foreach($serviceImages as $img)
                                    <div class="relative group">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img->image_path) }}" alt="Service" class="w-full h-24 object-cover rounded-lg border border-border" />
                                        @if(!empty($img->is_primary))
                                            <span class="absolute top-1 left-1 text-[9px] bg-primary text-primary-foreground px-1.5 py-0.5 rounded-md">Primary</span>
                                        @else
                                            <button type="button" class="absolute top-1 left-1 text-[9px] bg-secondary text-secondary-foreground px-1.5 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition-opacity"
                                                    onclick="setServicePrimaryImage('{{ $service->service_id }}', '{{ $img->image_id }}')">
                                                Set Primary
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif(!empty($service->image_path))
                            <div class="mb-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($service->image_path) }}" alt="Service" class="w-48 h-32 object-cover rounded-lg border border-border" />
                            </div>
                        @endif
                    @endif
                    <input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring" />
                    <p class="mt-2 text-[11px] text-muted-foreground">Select multiple images to upload. Recommended size: 800x600px.</p>
                </div>

                <!-- Fulfillment & Payment -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fulfillment_type" class="block text-sm font-medium mb-2">Fulfillment Type *</label>
                        <select id="fulfillment_type" name="fulfillment_type" required 
                                class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="pickup" {{ old('fulfillment_type', $service->fulfillment_type ?? 'pickup') === 'pickup' ? 'selected' : '' }}>Pickup Only</option>
                            <option value="delivery" {{ old('fulfillment_type', $service->fulfillment_type ?? '') === 'delivery' ? 'selected' : '' }}>Delivery Only</option>
                            <option value="both" {{ old('fulfillment_type', $service->fulfillment_type ?? '') === 'both' ? 'selected' : '' }}>Pickup & Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Allowed Payment Methods</label>
                        <div class="flex items-center gap-4 mt-3">
                            <x-ui.form.checkbox name="allowed_payment_methods[]" id="pm_gcash" value="gcash" 
                                :checked="in_array('gcash', old('allowed_payment_methods', $allowedPaymentMethods))" label="GCash" />
                            <x-ui.form.checkbox name="allowed_payment_methods[]" id="pm_cash" value="cash" 
                                :checked="in_array('cash', old('allowed_payment_methods', $allowedPaymentMethods))" label="Cash" />
                            <x-ui.form.checkbox name="allowed_payment_methods[]" id="pm_paypal" value="paypal" 
                                :checked="in_array('paypal', old('allowed_payment_methods', $allowedPaymentMethods))" label="PayPal" />
                        </div>
                    </div>
                </div>

                <!-- Toggles & Downpayment -->
                <div class="bg-muted/30 p-4 rounded-xl space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.form.switch name="is_active" id="is_active" 
                            :checked="old('is_active', $service->is_active ?? true)" label="Service is Active" />
                        
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'file_upload_enabled'))
                            <x-ui.form.switch name="file_upload_enabled" id="file_upload_enabled" 
                                :checked="old('file_upload_enabled', $service->file_upload_enabled ?? false)" label="Enable File Upload" />
                        @endif

                        @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload'))
                            <x-ui.form.switch name="requires_file_upload" id="requires_file_upload" 
                                :checked="old('requires_file_upload', $service->requires_file_upload ?? false)" label="Require File Upload" />
                        @endif

                        @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'supports_rush'))
                            <x-ui.form.switch name="supports_rush" id="supports_rush" 
                                :checked="old('supports_rush', $service->supports_rush ?? true)" label="Supports Rush Orders" />
                        @endif
                    </div>

                    @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_downpayment'))
                        <div class="pt-2 border-t border-border mt-2">
                            <x-ui.form.switch name="requires_downpayment" id="requires_downpayment" 
                                :checked="old('requires_downpayment', $service->requires_downpayment ?? false)" label="Require Downpayment" />
                            
                            <div class="mt-3 flex items-center gap-3">
                                <label for="downpayment_percent" class="text-sm font-medium">Downpayment Percent (%)</label>
                                <input type="number" id="downpayment_percent" name="downpayment_percent" 
                                       value="{{ old('downpayment_percent', $service->downpayment_percent ?? 0) }}" 
                                       step="0.01" min="0" max="100"
                                       class="w-24 px-3 py-1 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 pt-6">
                    <x-ui.tooltip text="Go back without saving changes">
                        <a href="{{ route('business.services.index') }}" 
                           class="inline-flex items-center justify-center gap-2 flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth focus:outline-none focus:ring-2 focus:ring-ring">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </a>
                    </x-ui.tooltip>
                    <x-ui.tooltip text="Save this service">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth focus:outline-none focus:ring-2 focus:ring-ring" data-up-button-loader>
                            <i data-lucide="save" class="h-4 w-4"></i>
                            {{ $isEdit ? 'Update Service' : 'Create Service' }}
                        </button>
                    </x-ui.tooltip>
                </div>
            </div>
        </form>
    </div>
</div>

@if($isEdit)
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
            window.UniPrintUI.toast(err.message || 'Error.', { variant: 'danger' });
        }
    }
}
</script>
@endif

<script>
    // Simple client-side validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endsection
