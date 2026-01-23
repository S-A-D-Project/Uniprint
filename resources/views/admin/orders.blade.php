@extends('layouts.admin-layout')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')
@section('page-subtitle', 'View and manage all customer orders')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Orders', 'url' => '#'],
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
<x-admin.card title="All Orders" icon="shopping-cart" :noPadding="true">
    <x-slot:actions>
        <x-admin.button size="sm" variant="ghost" icon="refresh-cw">
            Refresh
        </x-admin.button>
    </x-slot:actions>
    
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Enterprise</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="font-mono font-medium">#{{ $order->order_no }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <i data-lucide="user" class="h-4 w-4 text-muted-foreground"></i>
                            {{ $order->customer_name }}
                        </div>
                    </td>
                    <td>{{ $order->enterprise_name }}</td>
                    <td>
                        <x-admin.badge variant="secondary">Order Items</x-admin.badge>
                    </td>
                    <td class="font-medium">₱{{ number_format($order->total, 2) }}</td>
                    <td>
                        @if($order->status_name == 'Pending')
                            <x-admin.badge variant="warning" icon="clock">Pending</x-admin.badge>
                        @elseif($order->status_name == 'In Progress')
                            <x-admin.badge variant="info" icon="loader">In Progress</x-admin.badge>
                        @elseif(in_array($order->status_name, ['Delivered', 'Shipped'], true))
                            <x-admin.badge variant="success" icon="check-circle">Delivered</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary">{{ $order->status_name ?? 'Unknown' }}</x-admin.badge>
                        @endif
                    </td>
                    <td class="text-sm text-muted-foreground">
                        {{ date('M d, Y H:i', strtotime($order->created_at)) }}
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-admin.button size="sm" variant="ghost" icon="eye" href="{{ route('admin.orders.details', $order->purchase_order_id) }}" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <x-admin.empty-state 
                            icon="shopping-cart"
                            title="No orders found"
                            description="No orders have been placed yet" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->hasPages())
    <x-slot:customFooter>
        <div class="flex justify-center">
            {{ $orders->links() }}
        </div>
    </x-slot:customFooter>
    @endif
</x-admin.card>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
