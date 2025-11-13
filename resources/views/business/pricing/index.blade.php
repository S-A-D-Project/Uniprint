@extends('layouts.business')

@section('title', 'Pricing Rules')
@section('page-title', 'Pricing Rules')
@section('page-subtitle', 'Manage dynamic pricing and discounts')

@section('header-actions')
<a href="{{ route('business.pricing.create') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
    <i data-lucide="plus" class="h-4 w-4"></i>
    Add Rule
</a>
@endsection

@section('content')

    <div class="bg-card border border-border rounded-xl shadow-card overflow-hidden">
        @if($rules->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Rule Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($rules as $rule)
                            <tr class="hover:bg-secondary/30">
                                <td class="px-6 py-4 font-medium">{{ $rule->rule_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2 py-1 text-xs bg-primary/10 text-primary rounded-md">
                                        {{ str_replace('_', ' ', $rule->rule_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ ucfirst($rule->calculation_method) }}</td>
                                <td class="px-6 py-4 font-medium">
                                    @if($rule->calculation_method === 'percentage')
                                        {{ $rule->value }}%
                                    @else
                                        ₱{{ number_format($rule->value, 2) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $rule->priority }}</td>
                                <td class="px-6 py-4">
                                    @if($rule->is_active)
                                        <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md">Active</span>
                                    @else
                                        <span class="inline-block px-2 py-1 text-xs bg-destructive/10 text-destructive rounded-md">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('business.pricing.edit', $rule->rule_id) }}" 
                                           class="text-primary hover:text-primary/80 text-sm font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('business.pricing.delete', $rule->rule_id) }}" method="POST"
                                              onsubmit="return confirm('Delete this rule?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-destructive hover:text-destructive/80 text-sm font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($rules->hasPages())
                <div class="p-6 border-t border-border">
                    {{ $rules->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <i data-lucide="percent" class="h-24 w-24 mx-auto mb-4 text-muted-foreground"></i>
                <h3 class="text-xl font-bold mb-2">No Pricing Rules Yet</h3>
                <p class="text-muted-foreground mb-6">Create rules for volume discounts, bulk pricing, or special fees</p>
                <a href="{{ route('business.pricing.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Create First Rule
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
