@extends('layouts.admin-layout')

@section('title', 'Services Management')
@section('page-title', 'Services Management')
@section('page-subtitle', 'View all services across enterprises')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Services', 'url' => '#'],
];
@endphp

@section('header-actions')
    <x-admin.button variant="outline" icon="filter" size="sm">
        Filter
    </x-admin.button>
    <x-admin.button variant="outline" icon="download" size="sm">
        Export
    </x-admin.button>

    @if(request()->filled('enterprise_id'))
        <x-admin.button variant="primary" icon="plus-circle" size="sm" href="{{ route('business.services.create', ['enterprise_id' => request()->query('enterprise_id')]) }}" class="js-admin-service-create">
            New Service
        </x-admin.button>
    @else
        <x-admin.button variant="primary" icon="plus-circle" size="sm" :disabled="true">
            New Service
        </x-admin.button>
    @endif
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($services as $service)
    <x-admin.card :hover="true">
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-1">{{ $service->service_name ?? 'Unnamed Service' }}</h3>
                <p class="text-sm text-muted-foreground">{{ $service->enterprise_name ?? 'Unknown Enterprise' }}</p>
            </div>
            @if(($service->is_available ?? true))
                <x-admin.badge variant="success" icon="check-circle">Available</x-admin.badge>
            @else
                <x-admin.badge variant="secondary" icon="x-circle">Unavailable</x-admin.badge>
            @endif
        </div>

        <p class="text-sm text-muted-foreground mb-4">
            {{ Str::limit($service->description_text ?? 'No description available', 100) }}
        </p>

        <div class="flex justify-between items-center p-4 bg-success/10 rounded-lg">
            <div>
                <p class="text-xs text-muted-foreground mb-1">Base Price</p>
                <h4 class="text-2xl font-bold text-success">₱{{ number_format($service->base_price ?? 0, 2) }}</h4>
            </div>
            <i data-lucide="tag" class="h-8 w-8 text-success/50"></i>
        </div>

        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-border">
            <x-admin.button size="sm" variant="outline" icon="eye" class="flex-1" href="{{ route('admin.services.details', $service->service_id) }}">
                View Details
            </x-admin.button>

            <x-admin.button size="sm"
                           variant="outline"
                           icon="pencil"
                           href="{{ route('business.services.edit', $service->service_id) }}?enterprise_id={{ $service->enterprise_id }}"
                           class="js-admin-service-edit" />

            <form method="POST" action="{{ route('admin.services.toggle-active', $service->service_id) }}">
                @csrf
                @if(($service->is_available ?? true))
                    <x-admin.button size="sm" variant="ghost" icon="x-circle" type="submit" />
                @else
                    <x-admin.button size="sm" variant="ghost" icon="check-circle" type="submit" />
                @endif
            </form>
        </div>
    </x-admin.card>
    @empty
    <div class="col-span-full">
        <x-admin.card>
            <x-admin.empty-state 
                icon="package"
                title="No services found"
                description="No services have been created yet" />
        </x-admin.card>
    </div>
    @endforelse
</div>

@if($services->hasPages())
<div class="mt-6 flex justify-center">
    {{ $services->links() }}
</div>
@endif

<x-ui.modal id="adminServiceFormModal" title="Service" size="xl" scrollable>
    <div id="adminServiceFormModalBody" class="min-h-[200px]"></div>
</x-ui.modal>
@endsection

@push('scripts')
<script>
    lucide.createIcons();

    function openAdminServiceFormModal(url) {
        const modalEl = document.getElementById('adminServiceFormModal');
        const bodyEl = document.getElementById('adminServiceFormModalBody');
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
        })
        .catch((err) => {
            console.error(err);
            bodyEl.innerHTML = '<div class="p-4 text-danger">Failed to load service form.</div>';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-admin-service-create').forEach((a) => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = a.getAttribute('href');
                if (!url) return;
                openAdminServiceFormModal(url);
            });
        });

        document.querySelectorAll('.js-admin-service-edit').forEach((a) => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = a.getAttribute('href');
                if (!url) return;
                openAdminServiceFormModal(url);
            });
        });

        const modalEl = document.getElementById('adminServiceFormModal');
        const bodyEl = document.getElementById('adminServiceFormModalBody');
        if (!modalEl || !bodyEl) return;

        bodyEl.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (href && href.includes('/business/services')) {
                // prevent navigation back to business area when used inside admin modal
                e.preventDefault();
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
                    window.UniPrintUI.toast(err?.message || 'Failed to save service.', { variant: 'danger' });
                }
            }
        });
    });
</script>
@endpush
