@php
    $isEdit = isset($rule);
    $formAction = $isEdit ? route('business.pricing.update', $rule->rule_id) : route('business.pricing.store');
    $applyScope = old('apply_scope', ($isEdit && !empty($rule->service_id)) ? 'service' : 'all');
@endphp

<div class="p-6">
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Rule Name *</label>
                    <input type="text" name="rule_name" value="{{ old('rule_name', $rule->rule_name ?? '') }}" required
                           placeholder="e.g., Volume Discount, Rush Fee"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Rule Type *</label>
                    <select name="rule_type" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="">Select type</option>
                        <option value="volume_discount" {{ (old('rule_type', $rule->rule_type ?? '') == 'volume_discount') ? 'selected' : '' }}>Volume Discount</option>
                        <option value="bulk_pricing" {{ (old('rule_type', $rule->rule_type ?? '') == 'bulk_pricing') ? 'selected' : '' }}>Bulk Pricing</option>
                        <option value="rush_fee" {{ (old('rule_type', $rule->rule_type ?? '') == 'rush_fee') ? 'selected' : '' }}>Rush Fee</option>
                        <option value="shipping" {{ (old('rule_type', $rule->rule_type ?? '') == 'shipping') ? 'selected' : '' }}>Shipping</option>
                        <option value="seasonal" {{ (old('rule_type', $rule->rule_type ?? '') == 'seasonal') ? 'selected' : '' }}>Seasonal Pricing</option>
                        <option value="custom" {{ (old('rule_type', $rule->rule_type ?? '') == 'custom') ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Description</label>
                <textarea name="rule_description" rows="3"
                          class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('rule_description', $rule->rule_description ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Apply To *</label>
                    <select name="apply_scope" id="apply_scope" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="all" {{ $applyScope === 'all' ? 'selected' : '' }}>All orders</option>
                        <option value="service" {{ $applyScope === 'service' ? 'selected' : '' }}>Specific service orders</option>
                    </select>
                </div>

                <div id="serviceScopeField" style="{{ $applyScope === 'service' ? '' : 'display:none;' }}">
                    <label class="block text-sm font-medium mb-2">Service *</label>
                    <select name="service_id" id="service_id"
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="">Select service</option>
                        @foreach(($services ?? collect()) as $svc)
                            <option value="{{ $svc->service_id }}" {{ (string) old('service_id', $rule->service_id ?? '') === (string) $svc->service_id ? 'selected' : '' }}>
                                {{ $svc->service_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Calculation Method *</label>
                    <select name="calculation_method" id="calculation_method" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="">Select method</option>
                        <option value="percentage" {{ (old('calculation_method', $rule->calculation_method ?? '') == 'percentage') ? 'selected' : '' }}>Percentage (% of subtotal)</option>
                        <option value="fixed_amount" {{ (old('calculation_method', $rule->calculation_method ?? '') == 'fixed_amount') ? 'selected' : '' }}>Fixed Amount (₱)</option>
                        <option value="formula" {{ (old('calculation_method', $rule->calculation_method ?? '') == 'formula') ? 'selected' : '' }}>Custom Formula</option>
                    </select>
                </div>

                <div id="valueField" style="{{ (old('calculation_method', $rule->calculation_method ?? '') == 'formula') ? 'display:none;' : '' }}">
                    <label class="block text-sm font-medium mb-2">Value *</label>
                    <input type="number" name="value" step="0.01" value="{{ old('value', $rule->value ?? '') }}"
                           placeholder="e.g., 10 for 10% or 500 for ₱500"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Use negative values for discounts (e.g., -10 for 10% off)</p>
                </div>

                <div id="formulaField" style="{{ (old('calculation_method', $rule->calculation_method ?? '') == 'formula') ? '' : 'display:none;' }}">
                    <label class="block text-sm font-medium mb-2">Formula</label>
                    <input type="text" name="formula" value="{{ old('formula', $rule->formula ?? '') }}"
                           placeholder="e.g., {subtotal} * 0.15"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Use {subtotal} and {quantity} variables</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Conditions (Optional)</label>
                <div class="rounded-lg border border-border p-4 bg-background">
                    <div id="pricingRuleConditionsBuilder" class="space-y-2 mb-3"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="px-3 py-1.5 text-xs border border-input rounded-md hover:bg-secondary transition-smooth" onclick="window.UPPricingRule.addConditionRow(document.getElementById('pricingRuleConditionsBuilder'))">
                            Add Condition
                        </button>
                        <button type="button" class="px-3 py-1.5 text-xs border border-input rounded-md hover:bg-secondary transition-smooth" onclick="window.UPPricingRule.clearConditions(document.getElementById('pricingRuleConditionsBuilder'))">
                            Clear
                        </button>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="text-xs text-muted-foreground hover:text-foreground" onclick="window.UPPricingRule.toggleAdvancedJson()">
                            Advanced (JSON)
                        </button>
                        <div id="pricingRuleAdvancedJson" class="mt-2" style="display:none;">
                            <textarea name="conditions" id="pricingRuleConditionsJson" rows="3"
                                      class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring font-mono text-xs">{{ old('conditions', $rule->conditions ?? '[]') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Priority *</label>
                    <input type="number" name="priority" value="{{ old('priority', $rule->priority ?? '0') }}" required min="0"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Lower numbers execute first</p>
                </div>

                <div class="flex items-center pt-8">
                    <x-ui.form.switch
                        name="is_active"
                        id="is_active"
                        :checked="old('is_active', $rule->is_active ?? 1)"
                        label="Active"
                    />
                </div>
            </div>

            <div class="flex gap-3 justify-end pt-4">
                <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    {{ $isEdit ? 'Update Rule' : 'Create Rule' }}
                </button>
            </div>
        </div>
    </form>
</div>
