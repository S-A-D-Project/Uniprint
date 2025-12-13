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
            <x-admin.button size="sm" variant="outline" icon="eye" class="flex-1">
                View Details
            </x-admin.button>
            <x-admin.button size="sm" variant="ghost" icon="edit-2" />
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
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
