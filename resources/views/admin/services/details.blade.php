@extends('layouts.admin-layout')

@section('title', 'Service Details')
@section('page-title', 'Service Details')
@section('page-subtitle', 'Review service information and availability')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Services', 'url' => route('admin.services')],
    ['label' => 'Service Details', 'url' => '#'],
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card title="Service" icon="package">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Service ID</div>
                    <div class="font-mono font-semibold">{{ $service->service_id ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Enterprise</div>
                    <div class="font-semibold">
                        <a href="{{ route('admin.enterprises.details', $service->enterprise_id) }}" class="text-primary hover:underline">
                            {{ $service->enterprise_name ?? 'Enterprise' }}
                        </a>
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Name</div>
                    <div class="font-semibold">{{ $service->service_name ?? 'Unnamed Service' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Base Price</div>
                    <div class="text-lg font-bold text-success">₱{{ number_format($service->base_price ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="mt-4">
                <div class="text-sm text-muted-foreground mb-1">Description</div>
                <div class="text-sm">{{ $service->description_text ?? 'No description available.' }}</div>
            </div>
        </x-admin.card>

        <x-admin.card title="Usage" icon="bar-chart-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Orders containing this service</div>
                    <div class="text-2xl font-bold">{{ $orderCount ?? 0 }}</div>
                </div>
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Availability</div>
                    <div class="mt-1">
                        @if(($service->is_available ?? true))
                            <x-admin.badge variant="success" icon="check-circle">Available</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="x-circle">Unavailable</x-admin.badge>
                        @endif
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Actions" icon="settings">
            <div class="space-y-2">
                <form method="POST" action="{{ route('admin.services.toggle-active', $service->service_id) }}">
                    @csrf
                    @if(($service->is_available ?? true))
                        <x-admin.button type="submit" variant="destructive" icon="x-circle" class="w-full">Mark Unavailable</x-admin.button>
                    @else
                        <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Mark Available</x-admin.button>
                    @endif
                </form>

                <x-admin.button variant="outline" icon="building-2" href="{{ route('admin.enterprises.details', $service->enterprise_id) }}" class="w-full">View Enterprise</x-admin.button>

                <x-admin.button variant="outline" icon="shopping-cart" href="{{ route('admin.orders', ['enterprise_id' => $service->enterprise_id]) }}" class="w-full">View Enterprise Orders</x-admin.button>

                <x-admin.button variant="outline" icon="arrow-left" href="{{ route('admin.services') }}" class="w-full">Back to Services</x-admin.button>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
