@extends('layouts.business')

@section('title', 'Settings - ' . ($enterprise->name ?? 'Business'))
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage your account and print shop information')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-lg font-bold mb-4">Account Information</h2>
        <form action="{{ route('business.settings.account.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Position</label>
                    <input type="text" name="position" value="{{ old('position', $user->position ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                    Save Account
                </button>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-lg font-bold mb-4">Print Shop Information</h2>
        <form action="{{ route('business.settings.enterprise.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            @php
                $checkoutPaymentMethods = [];
                if (isset($enterprise->checkout_payment_methods)) {
                    $decoded = json_decode((string) $enterprise->checkout_payment_methods, true);
                    if (is_array($decoded)) {
                        $checkoutPaymentMethods = $decoded;
                    }
                }
                if (empty($checkoutPaymentMethods)) {
                    $checkoutPaymentMethods = ['cash'];
                }

                $checkoutFulfillmentMethods = [];
                if (isset($enterprise->checkout_fulfillment_methods)) {
                    $decoded = json_decode((string) $enterprise->checkout_fulfillment_methods, true);
                    if (is_array($decoded)) {
                        $checkoutFulfillmentMethods = $decoded;
                    }
                }
                if (empty($checkoutFulfillmentMethods)) {
                    $checkoutFulfillmentMethods = ['pickup'];
                }

                $rushOptions = [
                    'standard' => ['enabled' => true, 'fee' => 0, 'lead_hours' => 48],
                    'express' => ['enabled' => false, 'fee' => 50, 'lead_hours' => 24],
                    'rush' => ['enabled' => false, 'fee' => 100, 'lead_hours' => 6],
                    'same_day' => ['enabled' => false, 'fee' => 200, 'lead_hours' => 3],
                ];
                if (isset($enterprise->checkout_rush_options)) {
                    $decoded = json_decode((string) $enterprise->checkout_rush_options, true);
                    if (is_array($decoded)) {
                        foreach ($rushOptions as $k => $v) {
                            if (is_array($decoded[$k] ?? null)) {
                                $rushOptions[$k] = array_merge($v, $decoded[$k]);
                            }
                        }
                    }
                }
            @endphp

            <div>
                <label class="block text-sm font-medium mb-2">Shop Name</label>
                <input type="text" name="name" value="{{ old('name', $enterprise->name ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Address</label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('address', $enterprise->address ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Shop Email</label>
                    <input type="email" name="email" value="{{ old('email', $enterprise->email ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <input type="text" name="category" value="{{ old('category', $enterprise->category ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $enterprise->contact_person ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $enterprise->contact_number ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Shop Logo</label>
                <input type="file" name="shop_logo" accept="image/*"
                       class="w-full text-sm">
                @if(!empty($enterprise->shop_logo))
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $enterprise->shop_logo) }}" alt="Shop Logo" class="h-16 w-16 rounded-lg object-cover border border-border">
                    </div>
                @endif
            </div>

            @if(property_exists($enterprise, 'is_active') || isset($enterprise->is_active))
                <x-ui.form.switch
                    name="is_active"
                    id="is_active"
                    :checked="old('is_active', $enterprise->is_active ?? true)"
                    label="Shop is active"
                />
            @endif

            @if(isset($enterprise->checkout_payment_methods) || isset($enterprise->checkout_fulfillment_methods) || isset($enterprise->checkout_rush_options) || isset($enterprise->gcash_enabled) || isset($enterprise->gcash_instructions))
                <div class="pt-4 border-t border-border"></div>

                <div>
                    <h3 class="text-base font-semibold mb-3">Checkout Settings</h3>

                    @if(isset($enterprise->checkout_payment_methods))
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Allowed Payment Methods</label>
                            <div class="flex flex-wrap gap-4">
                                <x-ui.form.checkbox
                                    name="checkout_payment_methods[]"
                                    id="checkout_pm_cash"
                                    value="cash"
                                    :checked="in_array('cash', old('checkout_payment_methods', $checkoutPaymentMethods))"
                                    label="Cash"
                                />
                                <x-ui.form.checkbox
                                    name="checkout_payment_methods[]"
                                    id="checkout_pm_gcash"
                                    value="gcash"
                                    :checked="in_array('gcash', old('checkout_payment_methods', $checkoutPaymentMethods))"
                                    label="GCash"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">Only selected methods will show in checkout.</p>
                        </div>
                    @endif

                    @if(isset($enterprise->gcash_enabled) || isset($enterprise->gcash_instructions))
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(isset($enterprise->gcash_enabled))
                                <div>
                                    <x-ui.form.switch
                                        name="gcash_enabled"
                                        id="gcash_enabled"
                                        :checked="old('gcash_enabled', $enterprise->gcash_enabled ?? false)"
                                        label="Enable GCash (online payment)"
                                    />
                                    <p class="text-xs text-muted-foreground mt-1">If disabled, GCash will be shown but not clickable (when selected as allowed).</p>
                                </div>
                            @endif
                            @if(isset($enterprise->gcash_instructions))
                                <div>
                                    <label class="block text-sm font-medium mb-2">GCash Instructions</label>
                                    <textarea name="gcash_instructions" rows="3"
                                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('gcash_instructions', $enterprise->gcash_instructions ?? '') }}</textarea>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(isset($enterprise->checkout_fulfillment_methods))
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Allowed Fulfillment Methods</label>
                            <div class="flex flex-wrap gap-4">
                                <x-ui.form.checkbox
                                    name="checkout_fulfillment_methods[]"
                                    id="checkout_fm_pickup"
                                    value="pickup"
                                    :checked="in_array('pickup', old('checkout_fulfillment_methods', $checkoutFulfillmentMethods))"
                                    label="Pickup"
                                />
                                <x-ui.form.checkbox
                                    name="checkout_fulfillment_methods[]"
                                    id="checkout_fm_delivery"
                                    value="delivery"
                                    :checked="in_array('delivery', old('checkout_fulfillment_methods', $checkoutFulfillmentMethods))"
                                    label="Delivery"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">If delivery is enabled, checkout will ask for delivery address.</p>
                        </div>
                    @endif

                    @if(isset($enterprise->checkout_rush_options))
                        <div>
                            <label class="block text-sm font-medium mb-2">Rush Options</label>
                            <div class="space-y-3">
                                @php
                                    $rushLabels = [
                                        'standard' => 'Standard',
                                        'express' => 'Express',
                                        'rush' => 'Rush',
                                        'same_day' => 'Same Day',
                                    ];
                                @endphp
                                @foreach($rushLabels as $k => $label)
                                    @php
                                        $opt = old("rush_options.{$k}", $rushOptions[$k] ?? []);
                                        $optEnabled = !empty($opt['enabled']) || $k === 'standard';
                                    @endphp
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                                        <div class="md:col-span-3">
                                            <x-ui.form.checkbox
                                                name="rush_options[{{ $k }}][enabled]"
                                                id="rush_{{ $k }}_enabled"
                                                value="1"
                                                :checked="$optEnabled"
                                                :disabled="$k === 'standard'"
                                                label="{{ $label }}"
                                            />
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-medium mb-1">Fee (₱)</label>
                                            <input type="number" name="rush_options[{{ $k }}][fee]" step="0.01" min="0"
                                                   value="{{ old("rush_options.{$k}.fee", $opt['fee'] ?? 0) }}"
                                                   class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div class="md:col-span-5">
                                            <label class="block text-xs font-medium mb-1">Lead time (hours)</label>
                                            <input type="number" name="rush_options[{{ $k }}][lead_hours]" step="1" min="0"
                                                   value="{{ old("rush_options.{$k}.lead_hours", $opt['lead_hours'] ?? 0) }}"
                                                   class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-muted-foreground mt-2">Only enabled rush options will be shown in checkout.</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                    Save Print Shop
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
