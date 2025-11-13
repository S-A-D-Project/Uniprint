@extends('layouts.admin-layout')

@section('title', 'Products Management')
@section('page-title', 'Products Management')
@section('page-subtitle', 'View all products across enterprises')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Products', 'url' => '#'],
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
    @forelse($products as $product)
    <x-admin.card :hover="true">
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-1">{{ $product->product_name ?? 'Unnamed Product' }}</h3>
                <p class="text-sm text-muted-foreground">{{ $product->enterprise_name ?? 'Unknown Enterprise' }}</p>
            </div>
            @if(($product->is_available ?? true))
                <x-admin.badge variant="success" icon="check-circle">Available</x-admin.badge>
            @else
                <x-admin.badge variant="secondary" icon="x-circle">Unavailable</x-admin.badge>
            @endif
        </div>

        <p class="text-sm text-muted-foreground mb-4">
            {{ Str::limit($product->description_text ?? 'No description available', 100) }}
        </p>

        <div class="flex justify-between items-center p-4 bg-success/10 rounded-lg">
            <div>
                <p class="text-xs text-muted-foreground mb-1">Base Price</p>
                <h4 class="text-2xl font-bold text-success">₱{{ number_format($product->base_price ?? 0, 2) }}</h4>
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
                title="No products found"
                description="No products have been created yet" />
        </x-admin.card>
    </div>
    @endforelse
</div>

@if($products->hasPages())
<div class="mt-6 flex justify-center">
    {{ $products->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
