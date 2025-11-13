@extends('layouts.business')

@section('title', 'Edit Pricing Rule')
@section('page-title', 'Edit Pricing Rule')
@section('page-subtitle', 'Update rule configuration')

@section('header-actions')
<a href="{{ route('business.pricing.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Pricing Rules
</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <form action="{{ route('business.pricing.update', $rule->rule_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Rule Name *</label>
                    <input type="text" name="rule_name" value="{{ old('rule_name', $rule->rule_name) }}" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Rule Type *</label>
                    <select name="rule_type" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="volume_discount" {{ $rule->rule_type == 'volume_discount' ? 'selected' : '' }}>Volume Discount</option>
                        <option value="bulk_pricing" {{ $rule->rule_type == 'bulk_pricing' ? 'selected' : '' }}>Bulk Pricing</option>
                        <option value="rush_fee" {{ $rule->rule_type == 'rush_fee' ? 'selected' : '' }}>Rush Fee</option>
                        <option value="shipping" {{ $rule->rule_type == 'shipping' ? 'selected' : '' }}>Shipping</option>
                        <option value="seasonal" {{ $rule->rule_type == 'seasonal' ? 'selected' : '' }}>Seasonal Pricing</option>
                        <option value="custom" {{ $rule->rule_type == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="rule_description" rows="3"
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('rule_description', $rule->rule_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Calculation Method *</label>
                    <select name="calculation_method" id="calculation_method" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                            onchange="toggleFormulaField()">
                        <option value="percentage" {{ $rule->calculation_method == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed_amount" {{ $rule->calculation_method == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="formula" {{ $rule->calculation_method == 'formula' ? 'selected' : '' }}>Custom Formula</option>
                    </select>
                </div>

                <div id="valueField" style="{{ $rule->calculation_method == 'formula' ? 'display:none;' : '' }}">
                    <label class="block text-sm font-medium mb-2">Value *</label>
                    <input type="number" name="value" value="{{ old('value', $rule->value) }}" step="0.01"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div id="formulaField" style="{{ $rule->calculation_method != 'formula' ? 'display:none;' : '' }}">
                    <label class="block text-sm font-medium mb-2">Formula</label>
                    <input type="text" name="formula" value="{{ old('formula', $rule->formula) }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Conditions (JSON)</label>
                    <textarea name="conditions" rows="4"
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring font-mono text-sm">{{ old('conditions', $rule->conditions) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Priority *</label>
                    <input type="number" name="priority" value="{{ old('priority', $rule->priority) }}" required min="0"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $rule->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-primary rounded focus:ring-2 focus:ring-ring">
                    <label for="is_active" class="text-sm font-medium">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('business.pricing.index') }}" 
                       class="flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                        Update Rule
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFormulaField() {
    const method = document.getElementById('calculation_method').value;
    const valueField = document.getElementById('valueField');
    const formulaField = document.getElementById('formulaField');
    
    if (method === 'formula') {
        valueField.style.display = 'none';
        formulaField.style.display = 'block';
    } else {
        valueField.style.display = 'block';
        formulaField.style.display = 'none';
    }
}
</script>
@endsection
