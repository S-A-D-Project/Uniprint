@extends('layouts.public')

@section('title', ($service->service_name ?? 'Service') . ' - Printing Service')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('customer.enterprises') }}" class="hover:text-primary">Shops</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="hover:text-primary">{{ $service->enterprise->name ?? 'Unknown Shop' }}</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground">{{ $service->service_name }}</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div>
                    @if(!empty($service->image_path))
                        <div class="h-96 bg-secondary rounded-xl mb-4 overflow-hidden">
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="w-full h-full object-cover" />
                        </div>
                    @else
                        <div class="h-96 gradient-accent rounded-xl mb-4 flex items-center justify-center">
                            <i data-lucide="printer" class="h-32 w-32 text-white"></i>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="inline-block px-3 py-1 text-sm font-medium bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                                {{ $service->enterprise->name ?? 'Unknown Shop' }}
                            </a>

                            <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary">
                                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                                Back to shop
                            </a>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground rounded-md">Printing Service</span>
                            <span class="inline-block px-2 py-1 text-xs font-medium bg-success/10 text-success rounded-md">Available</span>
                        </div>

                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $service->service_name }}</h1>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Starting Price</p>
                            <div class="text-4xl font-bold text-primary">₱{{ number_format($service->base_price, 2) }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Price may vary based on customizations</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <form id="orderForm" action="{{ route('checkout.from-service') }}" method="POST" enctype="multipart/form-data" class="bg-card border border-border rounded-xl p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->service_id }}">

                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold">Order</h3>
                            <span class="text-2xl font-bold text-primary">₱<span id="totalPrice">{{ number_format($service->base_price, 2) }}</span></span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="quantity">Quantity</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="changeQuantity(-1)" class="inline-flex items-center justify-center h-10 w-10 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                    <i data-lucide="minus" class="h-4 w-4"></i>
                                </button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="100" class="w-20 text-center text-xl font-bold border border-input rounded-md py-2" onchange="updatePrice()">
                                <button type="button" onclick="changeQuantity(1)" class="inline-flex items-center justify-center h-10 w-10 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                    <i data-lucide="plus" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="border border-border rounded-lg p-4 space-y-6 max-h-[420px] overflow-y-auto">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-semibold">Service Options</h4>
                                <p class="text-xs text-muted-foreground">No customizations selected by default.</p>
                            </div>

                            @if(isset($customizationGroups) && $customizationGroups->count() > 0)
                                @foreach($customizationGroups as $type => $options)
                                    @php
                                        $groupKey = \Illuminate\Support\Str::slug($type);
                                    @endphp
                                    <div>
                                        <div class="text-sm font-medium mb-2">{{ $type }}</div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @foreach($options as $option)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="customizations[{{ $groupKey }}]" value="{{ $option->option_id }}" class="sr-only peer" data-price="{{ $option->price_modifier }}" onchange="updatePrice()">
                                                    <div class="border border-input rounded-md px-3 py-2 text-sm hover:bg-secondary transition-smooth peer-checked:border-primary peer-checked:bg-primary/5">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="line-clamp-1">{{ $option->option_name }}</span>
                                                            @if($option->price_modifier != 0)
                                                                <span class="text-xs font-medium {{ $option->price_modifier > 0 ? 'text-primary' : 'text-success' }}">
                                                                    {{ $option->price_modifier > 0 ? '+' : '' }}₱{{ number_format($option->price_modifier, 2) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(($service->customFields ?? collect())->count() > 0)
                                <div>
                                    <div class="text-sm font-medium mb-2">Additional Information</div>
                                    <div class="space-y-3">
                                        @foreach($service->customFields as $field)
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="custom_field_{{ $field->field_id }}">
                                                    {{ $field->field_label }}@if($field->is_required)<span class="text-destructive"> *</span>@endif
                                                </label>
                                                <input
                                                    type="text"
                                                    id="custom_field_{{ $field->field_id }}"
                                                    name="custom_fields[{{ $field->field_id }}]"
                                                    value=""
                                                    {{ $field->is_required ? 'required' : '' }}
                                                    placeholder="{{ $field->placeholder ?? '' }}"
                                                    class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @php
                                $requiresFileUpload = \Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload')
                                    ? !empty($service->requires_file_upload)
                                    : false;

                                $uploadEnabled = \Illuminate\Support\Facades\Schema::hasColumn('services', 'file_upload_enabled')
                                    ? !empty($service->file_upload_enabled)
                                    : $requiresFileUpload;

                                if ($requiresFileUpload) {
                                    $uploadEnabled = true;
                                }
                            @endphp

                            <div class="border border-border rounded-lg p-4 bg-secondary/30">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="text-sm font-medium">Design Files</div>
                                    @if($requiresFileUpload)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-destructive/10 text-destructive rounded-md">
                                            Required
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-muted text-muted-foreground rounded-md">
                                            Optional
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-muted-foreground mb-3">
                                    @if($requiresFileUpload)
                                        This service requires design files. Upload them here so the shop can start processing immediately.
                                    @elseif($uploadEnabled)
                                        You can upload design files now (optional) or later from the order details page.
                                    @else
                                        File uploads are not enabled for this service.
                                    @endif
                                </p>

                                @if($uploadEnabled)
                                    <div class="space-y-3">
                                        <div>
                                            <x-ui.form.file-dropzone
                                                name="design_files[]"
                                                id="design_files"
                                                label="Upload design files"
                                                :required="$requiresFileUpload"
                                                :multiple="true"
                                                accept=".jpg,.jpeg,.png,.pdf,.ai,.psd,.eps,.svg"
                                                help="Accepted: JPG, PNG, PDF, AI, PSD, EPS, SVG. Max 50MB each."
                                                buttonText="Choose Files"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2" for="design_notes">Notes (optional)</label>
                                            <textarea name="design_notes" id="design_notes" rows="2" class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none" placeholder="Any notes for the shop about these files..."></textarea>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('customer.design-assets') }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                        <i data-lucide="images" class="h-4 w-4"></i>
                                        My Designs
                                    </a>
                                    <a href="{{ route('customer.orders') }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                        <i data-lucide="package" class="h-4 w-4"></i>
                                        My Orders
                                    </a>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="notes">Special Instructions (Optional)</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none" placeholder="Any special requirements or notes for this order..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-primary text-primary-foreground py-3 px-6 rounded-lg font-semibold hover:bg-primary/90 transition-colors">
                            Order Now
                        </button>

                        <button type="button" onclick="saveServiceFromDetails()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                            <i data-lucide="heart" class="h-4 w-4"></i>
                            Save Service
                        </button>
                    </form>

                    <div class="bg-card border border-border rounded-xl p-6">
                        <h4 class="text-lg font-semibold mb-3">Shop Details</h4>
                        <div class="space-y-2 text-sm text-muted-foreground">
                            @if(!empty($service->enterprise->address ?? null))
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="h-4 w-4"></i>
                                    <span>{{ $service->enterprise->address }}</span>
                                </div>
                            @endif
                            @if(!empty($service->enterprise->contact_number ?? null))
                                <div class="flex items-center gap-2">
                                    <i data-lucide="phone" class="h-4 w-4"></i>
                                    <span>{{ $service->enterprise->contact_number }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 bg-card border border-border rounded-xl p-6">
                <h3 class="text-xl font-bold mb-3">Service Description</h3>
                <div class="text-muted-foreground leading-relaxed whitespace-pre-line">
                    {{ $service->description ?? 'No description available.' }}
                </div>
            </div>
        </main>
    </div>

<script>
const basePrice = {{ (float) $service->base_price }};

function changeQuantity(change) {
    const input = document.getElementById('quantity');
    if (!input) return;

    const next = Math.max(1, parseInt(input.value || '1', 10) + change);
    input.value = String(next);
    updatePrice();
}

function updatePrice() {
    const qty = parseInt(document.getElementById('quantity')?.value || '1', 10);
    let total = basePrice;

    document.querySelectorAll('#orderForm input[name^="customizations"]:checked').forEach((input) => {
        const price = parseFloat(input.dataset.price || '0');
        if (!Number.isNaN(price)) total += price;
    });

    total *= qty;
    const el = document.getElementById('totalPrice');
    if (el) el.textContent = total.toFixed(2);
}

function saveServiceFromDetails() {
    const form = document.getElementById('orderForm');
    if (!form) return;

    const serviceId = form.querySelector('input[name="service_id"]')?.value;
    const quantity = parseInt(form.querySelector('input[name="quantity"]')?.value || '1', 10);
    const notes = form.querySelector('textarea[name="notes"]')?.value || '';
    const customizations = Array.from(document.querySelectorAll('#orderForm input[name^="customizations"]:checked')).map(i => i.value);

    for (const input of Array.from(form.querySelectorAll('input[name^="custom_fields["]'))) {
        if (input.required && String(input.value || '').trim() === '') {
            alert('Please fill in required fields.');
            input.focus();
            return;
        }
    }

    const custom_fields = {};
    for (const input of Array.from(form.querySelectorAll('input[name^="custom_fields["]'))) {
        const match = input.name.match(/^custom_fields\[(.+)\]$/);
        if (!match) continue;
        const key = match[1];
        const value = String(input.value || '').trim();
        if (value !== '') custom_fields[key] = value;
    }

    fetch('/saved-services/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            service_id: serviceId,
            quantity,
            customizations,
            custom_fields,
            notes
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = "{{ route('saved-services.index') }}";
        } else {
            alert(data.message || 'Failed to save service');
        }
    })
    .catch(() => alert('Failed to save service'));
}

document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});
</script>
@endsection
