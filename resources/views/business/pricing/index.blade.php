@extends('layouts.business')

@section('title', 'Pricing Rules')
@section('page-title', 'Pricing Rules')
@section('page-subtitle', 'Manage dynamic pricing and discounts')

@section('header-actions')
<a href="{{ route('business.pricing.create') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth js-pricing-create">
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
                                           class="text-primary hover:text-primary/80 text-sm font-medium js-pricing-edit"
                                           data-pricing-edit-url="{{ route('business.pricing.edit', $rule->rule_id) }}">
                                            Edit
                                        </a>
                                        <form id="delete-pricing-rule-{{ $rule->rule_id }}" action="{{ route('business.pricing.delete', $rule->rule_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="text-destructive hover:text-destructive/80 text-sm font-medium"
                                                    onclick="confirmPricingRuleDelete('{{ $rule->rule_id }}', @json($rule->rule_name))">
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
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth js-pricing-create">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Create First Rule
                </a>
            </div>
        @endif
    </div>
</div>

<x-ui.modal id="pricingRuleModal" title="Pricing Rule" size="xl" scrollable>
    <div id="pricingRuleModalBody" class="min-h-[200px]"></div>
</x-ui.modal>

<x-modals.confirm-action />
@endsection

@push('scripts')
<script>
function openPricingRuleModal(url) {
    const modalEl = document.getElementById('pricingRuleModal');
    const bodyEl = document.getElementById('pricingRuleModalBody');
    if (!modalEl || !bodyEl) return;

    modalEl.dataset.currentFormUrl = url;
    bodyEl.innerHTML = '<div class="p-4 text-muted">Loading...</div>';

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(async (res) => {
        if (!res.ok) throw new Error('Failed to load');
        return await res.text();
    })
    .then((html) => {
        bodyEl.innerHTML = html || '<div class="p-4 text-muted">No content</div>';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const methodEl = bodyEl.querySelector('#calculation_method');
        if (methodEl) {
            try {
                toggleFormulaField();
            } catch (e) {
                // no-op
            }
        }
    })
    .catch((err) => {
        console.error(err);
        bodyEl.innerHTML = '<div class="p-4 text-danger">Failed to load pricing rule form.</div>';
    });
}

function confirmPricingRuleDelete(ruleId, ruleName) {
    if (typeof window.showConfirmModal !== 'function') return;

    showConfirmModal({
        title: 'Delete Pricing Rule',
        message: `Are you sure you want to delete "${ruleName}"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
        callback: async () => {
            document.getElementById(`delete-pricing-rule-${ruleId}`).submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-pricing-create').forEach((a) => {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            const url = a.getAttribute('href');
            if (!url) return;
            openPricingRuleModal(url);
        });
    });

    document.querySelectorAll('.js-pricing-edit').forEach((a) => {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            const url = a.getAttribute('data-pricing-edit-url') || a.getAttribute('href');
            if (!url) return;
            openPricingRuleModal(url);
        });
    });

    const modalEl = document.getElementById('pricingRuleModal');
    const bodyEl = document.getElementById('pricingRuleModalBody');
    if (!modalEl || !bodyEl) return;

    bodyEl.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (href && href === `{{ route('business.pricing.index') }}`) {
            e.preventDefault();
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    });

    bodyEl.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        e.preventDefault();

        const url = form.getAttribute('action');
        if (!url) return;

        try {
            const res = await fetch(url, {
                method: (form.getAttribute('method') || 'POST').toUpperCase(),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const data = await res.json().catch(() => null);
            if (!res.ok || !data || data.success !== true) {
                throw new Error(data?.message || 'Request failed');
            }

            if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                window.UniPrintUI.toast(data.message || 'Saved.', { variant: 'success' });
            }

            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            window.location.reload();
        } catch (err) {
            console.error(err);
            if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                window.UniPrintUI.toast(err?.message || 'Failed to save pricing rule.', { variant: 'danger' });
            }
        }
    });
});
</script>
@endpush
