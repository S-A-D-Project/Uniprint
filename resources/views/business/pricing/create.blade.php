@extends('layouts.business')

@section('title', 'Create Pricing Rule')
@section('page-title', 'Create Pricing Rule')
@section('page-subtitle', 'Define dynamic pricing for your products')

@section('header-actions')
<a href="{{ route('business.pricing.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Pricing Rules
</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <form action="{{ route('business.pricing.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Rule Name *</label>
                    <input type="text" name="rule_name" required
                           placeholder="e.g., Volume Discount, Rush Fee"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Rule Type *</label>
                    <select name="rule_type" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="">Select type</option>
                        <option value="volume_discount">Volume Discount</option>
                        <option value="bulk_pricing">Bulk Pricing</option>
                        <option value="rush_fee">Rush Fee</option>
                        <option value="shipping">Shipping</option>
                        <option value="seasonal">Seasonal Pricing</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="rule_description" rows="3"
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Calculation Method *</label>
                    <select name="calculation_method" id="calculation_method" required
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                            onchange="toggleFormulaField()">
                        <option value="">Select method</option>
                        <option value="percentage">Percentage (% of subtotal)</option>
                        <option value="fixed_amount">Fixed Amount (₱)</option>
                        <option value="formula">Custom Formula</option>
                    </select>
                </div>

                <div id="valueField">
                    <label class="block text-sm font-medium mb-2">Value *</label>
                    <input type="number" name="value" step="0.01" required
                           placeholder="e.g., 10 for 10% or 500 for ₱500"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Use negative values for discounts (e.g., -10 for 10% off)</p>
                </div>

                <div id="formulaField" style="display:none;">
                    <label class="block text-sm font-medium mb-2">Formula</label>
                    <input type="text" name="formula"
                           placeholder="e.g., {subtotal} * 0.15"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Use {subtotal} and {quantity} variables</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Conditions (JSON)</label>
                    <textarea name="conditions" rows="4" placeholder='[{"field":"quantity","operator":">=","value":10}]'
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring font-mono text-sm"></textarea>
                    <p class="text-xs text-muted-foreground mt-1">Optional: Define when this rule applies</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Priority *</label>
                    <input type="number" name="priority" value="0" required min="0"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                    <p class="text-xs text-muted-foreground mt-1">Lower numbers execute first</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="h-4 w-4 text-primary rounded focus:ring-2 focus:ring-ring">
                    <label for="is_active" class="text-sm font-medium">Active (rule will be applied to orders)</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('business.pricing.index') }}" 
                       class="flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                        Create Rule
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
        valueField.querySelector('input').required = false;
    } else {
        valueField.style.display = 'block';
        formulaField.style.display = 'none';
        valueField.querySelector('input').required = true;
    }
}
</script>
@endsection
