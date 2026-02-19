@extends('layouts.public')

@section('title', ($service->service_name ?? 'Service') . ' - Printing Service')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <nav class="flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('customer.marketplace') }}" class="hover:text-primary">Shops</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="hover:text-primary">{{ $service->enterprise->name ?? 'Unknown Shop' }}</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground font-medium">{{ $service->service_name }}</span>
                </nav>
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

                            <div class="flex items-center gap-2">
                                <x-ui.tooltip text="Go back to the shop">
                                    <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary">
                                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                                        Back to shop
                                    </a>
                                </x-ui.tooltip>

                                <x-ui.tooltip text="Report this service to admin">
                                    <button type="button" class="inline-flex items-center gap-2 text-sm border border-destructive text-destructive px-3 py-2 rounded-md hover:bg-destructive/10 transition-smooth" data-up-report data-entity-type="service" data-service-id="{{ $service->service_id }}">
                                        <i data-lucide="flag" class="h-4 w-4"></i>
                                        Report
                                    </button>
                                </x-ui.tooltip>
                            </div>
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
                                    <div>
                                        <div class="text-sm font-medium mb-2">{{ $type }}</div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @php
                                                $oldSelected = old('customizations', []);
                                                if (!is_array($oldSelected)) {
                                                    $oldSelected = [];
                                                }
                                            @endphp
                                            @foreach($options as $option)
                                                @php
                                                    $isChecked = in_array($option->option_id, $oldSelected, true);
                                                @endphp
                                                <label class="cursor-pointer">
                                                    <input type="checkbox" name="customizations[]" value="{{ $option->option_id }}" class="sr-only peer" data-price="{{ $option->price_modifier }}" data-option-type="{{ $option->option_type }}" data-option-name="{{ $option->option_name }}" onchange="updatePrice()" {{ $isChecked ? 'checked' : '' }}>
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

                                        @php
                                            $supportsCustomSize = \Illuminate\Support\Facades\Schema::hasColumn('services', 'supports_custom_size')
                                                ? !empty($service->supports_custom_size)
                                                : false;
                                            $unit = $service->custom_size_unit ?? '';
                                            $minW = $service->custom_size_min_width ?? null;
                                            $maxW = $service->custom_size_max_width ?? null;
                                            $minH = $service->custom_size_min_height ?? null;
                                            $maxH = $service->custom_size_max_height ?? null;
                                        @endphp

                                        @if($supportsCustomSize && strtolower((string) $type) === 'size')
                                            <div id="customSizeFields" class="mt-3 hidden" data-service-unit="{{ $unit }}" data-min-w="{{ $minW }}" data-max-w="{{ $maxW }}" data-min-h="{{ $minH }}" data-max-h="{{ $maxH }}">
                                                <div class="text-sm font-medium mb-2">Custom Size</div>

                                                <div class="mb-3">
                                                    <label class="block text-xs text-muted-foreground mb-1" for="custom_size_unit_select">Unit</label>
                                                    @php
                                                        $serviceUnit = is_string($unit) && $unit !== '' ? strtolower($unit) : 'in';
                                                    @endphp
                                                    <select id="custom_size_unit_select" class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                                                        <option value="in" {{ $serviceUnit === 'in' ? 'selected' : '' }}>in</option>
                                                        <option value="cm" {{ $serviceUnit === 'cm' ? 'selected' : '' }}>cm</option>
                                                        <option value="mm" {{ $serviceUnit === 'mm' ? 'selected' : '' }}>mm</option>
                                                    </select>
                                                    <div class="mt-1 text-[11px] text-muted-foreground">You can enter in any unit. We will convert it automatically.</div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs text-muted-foreground mb-1" for="custom_size_width">Width <span id="custom_size_unit_label_w">{{ $unit ? "({$unit})" : '' }}</span></label>
                                                        <input type="number"
                                                               id="custom_size_width"
                                                               name="custom_fields[custom_size_width]"
                                                               step="0.01"
                                                               class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                                                               placeholder="" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-muted-foreground mb-1" for="custom_size_height">Height <span id="custom_size_unit_label_h">{{ $unit ? "({$unit})" : '' }}</span></label>
                                                        <input type="number"
                                                               id="custom_size_height"
                                                               name="custom_fields[custom_size_height]"
                                                               step="0.01"
                                                               class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                                                               placeholder="" />
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-xs text-muted-foreground" id="custom_size_range_help">Enter width and height within the allowed range.</div>
                                            </div>
                                        @endif
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
                                    <button type="button"
                                            onclick="scrollToDesignUpload()"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth {{ $uploadEnabled ? '' : 'opacity-50 cursor-not-allowed' }}"
                                            {{ $uploadEnabled ? '' : 'disabled' }}>
                                        <i data-lucide="upload" class="h-4 w-4"></i>
                                        Upload
                                    </button>
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

            <div class="mt-6 bg-card border border-border rounded-xl p-6">
                <h3 class="text-xl font-bold mb-3">Reviews</h3>
                @if(isset($reviews) && $reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                            <div class="border border-border rounded-lg p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold">{{ $review->customer_name ?? 'Customer' }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= (int) ($review->rating ?? 0))
                                                    <span class="text-yellow-500">★</span>
                                                @else
                                                    <span class="text-muted-foreground">★</span>
                                                @endif
                                            @endfor
                                            @if(!empty($review->created_at))
                                                <span class="ml-2">{{ is_string($review->created_at) ? date('M d, Y', strtotime($review->created_at)) : \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($review->comment))
                                    <div class="mt-3 text-sm text-muted-foreground whitespace-pre-line">{{ $review->comment }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-muted-foreground">No reviews yet.</div>
                @endif
            </div>
        </main>
    </div>

<script>
const basePrice = {{ (float) $service->base_price }};

function scrollToDesignUpload() {
    const el = document.getElementById('design_files');
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    try { el.focus(); } catch (e) {}
}

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

    document.querySelectorAll('#orderForm input[name="customizations[]"]:checked').forEach((input) => {
        const price = parseFloat(input.dataset.price || '0');
        if (!Number.isNaN(price)) total += price;
    });

    total *= qty;
    const el = document.getElementById('totalPrice');
    if (el) el.textContent = total.toFixed(2);

    updateCustomSizeVisibility();
}

function isCustomSizeSelected() {
    const selected = Array.from(document.querySelectorAll('#orderForm input[name="customizations[]"]:checked[data-option-type][data-option-name]'));
    if (!selected.length) return false;
    return selected.some((el) => {
        const type = String(el.dataset.optionType || '').trim().toLowerCase();
        const name = String(el.dataset.optionName || '').trim().toLowerCase();
        return type === 'size' && name === 'custom size';
    });
}

function updateCustomSizeVisibility() {
    const wrap = document.getElementById('customSizeFields');
    if (!wrap) return;

    const selected = isCustomSizeSelected();
    wrap.classList.toggle('hidden', !selected);

    const w = document.getElementById('custom_size_width');
    const h = document.getElementById('custom_size_height');
    if (w) w.required = selected;
    if (h) h.required = selected;

    if (selected) {
        refreshCustomSizeUnitUI();
    }
}

function convertUnit(value, fromUnit, toUnit) {
    const v = parseFloat(String(value));
    if (Number.isNaN(v)) return null;

    const from = String(fromUnit || '').toLowerCase();
    const to = String(toUnit || '').toLowerCase();
    if (from === '' || to === '' || from === to) return v;

    const toInches = (val, unit) => {
        if (unit === 'in') return val;
        if (unit === 'cm') return val / 2.54;
        if (unit === 'mm') return val / 25.4;
        return val;
    };
    const fromInches = (val, unit) => {
        if (unit === 'in') return val;
        if (unit === 'cm') return val * 2.54;
        if (unit === 'mm') return val * 25.4;
        return val;
    };

    const inVal = toInches(v, from);
    return fromInches(inVal, to);
}

function refreshCustomSizeUnitUI() {
    const wrap = document.getElementById('customSizeFields');
    if (!wrap) return;

    const serviceUnit = String(wrap.dataset.serviceUnit || 'in').toLowerCase();
    const select = document.getElementById('custom_size_unit_select');
    const chosen = String(select?.value || serviceUnit).toLowerCase();

    const labelW = document.getElementById('custom_size_unit_label_w');
    const labelH = document.getElementById('custom_size_unit_label_h');
    if (labelW) labelW.textContent = chosen ? `(${chosen})` : '';
    if (labelH) labelH.textContent = chosen ? `(${chosen})` : '';

    const minW = wrap.dataset.minW !== '' ? parseFloat(String(wrap.dataset.minW || '')) : null;
    const maxW = wrap.dataset.maxW !== '' ? parseFloat(String(wrap.dataset.maxW || '')) : null;
    const minH = wrap.dataset.minH !== '' ? parseFloat(String(wrap.dataset.minH || '')) : null;
    const maxH = wrap.dataset.maxH !== '' ? parseFloat(String(wrap.dataset.maxH || '')) : null;

    const w = document.getElementById('custom_size_width');
    const h = document.getElementById('custom_size_height');

    const minWDisp = minW !== null ? convertUnit(minW, serviceUnit, chosen) : null;
    const maxWDisp = maxW !== null ? convertUnit(maxW, serviceUnit, chosen) : null;
    const minHDisp = minH !== null ? convertUnit(minH, serviceUnit, chosen) : null;
    const maxHDisp = maxH !== null ? convertUnit(maxH, serviceUnit, chosen) : null;

    if (w) {
        w.placeholder = minWDisp !== null && maxWDisp !== null ? `${minWDisp.toFixed(2)} - ${maxWDisp.toFixed(2)}` : '';
    }
    if (h) {
        h.placeholder = minHDisp !== null && maxHDisp !== null ? `${minHDisp.toFixed(2)} - ${maxHDisp.toFixed(2)}` : '';
    }

    const help = document.getElementById('custom_size_range_help');
    if (help && minWDisp !== null && maxWDisp !== null && minHDisp !== null && maxHDisp !== null) {
        help.textContent = `Allowed range: ${minWDisp.toFixed(2)}-${maxWDisp.toFixed(2)} x ${minHDisp.toFixed(2)}-${maxHDisp.toFixed(2)} ${chosen}`;
    }
}

function saveServiceFromDetails() {
    const form = document.getElementById('orderForm');
    if (!form) return;

    const serviceId = form.querySelector('input[name="service_id"]')?.value;
    const quantity = parseInt(form.querySelector('input[name="quantity"]')?.value || '1', 10);
    const notes = form.querySelector('textarea[name="notes"]')?.value || '';
    const customizations = Array.from(document.querySelectorAll('#orderForm input[name="customizations[]"]:checked')).map(i => i.value);

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
        let value = String(input.value || '').trim();
        if (value === '') continue;

        if ((key === 'custom_size_width' || key === 'custom_size_height') && isCustomSizeSelected()) {
            const wrap = document.getElementById('customSizeFields');
            const serviceUnit = String(wrap?.dataset.serviceUnit || 'in').toLowerCase();
            const chosenUnit = String(document.getElementById('custom_size_unit_select')?.value || serviceUnit).toLowerCase();
            const conv = convertUnit(value, chosenUnit, serviceUnit);
            if (conv !== null) {
                value = String(conv.toFixed(2));
            }
        }

        custom_fields[key] = value;
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

    const unitSelect = document.getElementById('custom_size_unit_select');
    if (unitSelect) {
        unitSelect.addEventListener('change', function () {
            refreshCustomSizeUnitUI();
        });
    }

    const form = document.getElementById('orderForm');
    if (form) {
        form.addEventListener('submit', function () {
            const wrap = document.getElementById('customSizeFields');
            if (!wrap) return;
            if (!isCustomSizeSelected()) return;

            const serviceUnit = String(wrap.dataset.serviceUnit || 'in').toLowerCase();
            const chosenUnit = String(document.getElementById('custom_size_unit_select')?.value || serviceUnit).toLowerCase();
            if (serviceUnit === chosenUnit) return;

            const w = document.getElementById('custom_size_width');
            const h = document.getElementById('custom_size_height');
            if (!w || !h) return;

            const wConv = convertUnit(w.value, chosenUnit, serviceUnit);
            const hConv = convertUnit(h.value, chosenUnit, serviceUnit);
            if (wConv !== null) w.value = String(wConv.toFixed(2));
            if (hConv !== null) h.value = String(hConv.toFixed(2));
        });
    }
});
</script>
@endsection
