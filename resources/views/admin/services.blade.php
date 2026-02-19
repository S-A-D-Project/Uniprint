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

        <div class="flex items-center gap-2">
            <x-ui.tooltip text="View service details">
                <button type="button"
                       onclick="openAdminServiceFormModal('{{ route('admin.services.details', $service->service_id) }}')"
                       class="p-2 hover:bg-secondary rounded-md transition-colors">
                    <i data-lucide="eye" class="h-4 w-4 text-primary"></i>
                </button>
            </x-ui.tooltip>

            <x-ui.tooltip text="Edit this service">
                <button type="button"
                       onclick="openAdminServiceFormModal('{{ route('business.services.edit', $service->service_id) }}?enterprise_id={{ $service->enterprise_id }}')"
                       class="p-2 hover:bg-secondary rounded-md transition-colors">
                    <i data-lucide="pencil" class="h-4 w-4 text-primary"></i>
                </button>
            </x-ui.tooltip>

            <x-ui.tooltip text="{{ ($service->is_available ?? true) ? 'Deactivate this service' : 'Activate this service' }}">
                <form method="POST" action="{{ route('admin.services.toggle-active', $service->service_id) }}">
                    @csrf
                    @if(($service->is_available ?? true))
                        <x-admin.button size="sm" variant="ghost" icon="x-circle" type="submit" />
                    @else
                        <x-admin.button size="sm" variant="ghost" icon="check-circle" type="submit" />
                    @endif
                </form>
            </x-ui.tooltip>
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

    const __upModalCache = (window.__upModalCache = window.__upModalCache || {});

function openAdminServiceFormModal(url) {
    const modalEl = document.getElementById('adminServiceFormModal');
    const bodyEl = document.getElementById('adminServiceFormModalBody');
    if (!modalEl || !bodyEl) return;

    bodyEl.innerHTML = '<div class="py-10 text-center text-muted-foreground">Loading…</div>';

    let bsModal = window.modal_adminServiceFormModal;
    if (!bsModal && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalEl);
        window.modal_adminServiceFormModal = bsModal;
    }
    if (bsModal) bsModal.show();

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    })
    .then(res => res.text())
    .then(html => {
        bodyEl.innerHTML = html;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    })
    .catch(err => {
        bodyEl.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
    });
}
</script>
@endpush
