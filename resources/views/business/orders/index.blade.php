@extends('layouts.business')

@section('title', 'Orders - ' . $enterprise->name)
@section('page-title', 'Orders Management')
@section('page-subtitle', 'Manage and track all customer orders')

@section('content')

    <!-- Stats Cards -->
    <div class="grid md:grid-cols-4 gap-6 mb-6">
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-muted-foreground">Total Orders</span>
                <i data-lucide="shopping-bag" class="h-5 w-5 text-primary"></i>
            </div>
            <p class="text-3xl font-bold">{{ $orders->total() }}</p>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-card border border-border rounded-xl shadow-card overflow-hidden">
        <div class="p-6 border-b border-border">
            <h2 class="text-xl font-bold">All Orders</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($orders as $order)
                        <tr class="hover:bg-secondary/30">
                            <td class="px-6 py-4 font-medium">{{ $order->order_no }}</td>
                            <td class="px-6 py-4">{{ $order->customer_name }}</td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ date('M d, Y', strtotime($order->created_at)) }}
                            </td>
                            <td class="px-6 py-4 font-medium">₱{{ number_format($order->total, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($order->status_name)
                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-md
                                        @if($order->status_name == 'Pending') bg-warning/10 text-warning
                                        @elseif($order->status_name == 'Confirmed') bg-blue-500/10 text-blue-500
                                        @elseif($order->status_name == 'In Progress') bg-primary/10 text-primary
                                        @elseif($order->status_name == 'Delivered') bg-success/10 text-success
                                        @else bg-secondary text-secondary-foreground
                                        @endif">
                                        {{ $order->status_name }}
                                    </span>
                                @else
                                    <span class="text-muted-foreground text-sm">No status</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('business.orders.details', $order->purchase_order_id) }}" 
                                   class="text-primary hover:text-primary/80 font-medium text-sm">
                                    View Details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                <i data-lucide="inbox" class="h-12 w-12 mx-auto mb-4 text-muted-foreground"></i>
                                <p>No orders yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="p-6 border-t border-border">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
