@extends('layouts.business')

@section('title', 'Business Dashboard')
@section('page-title', $enterprise->name ?? 'Business Dashboard')
@section('page-subtitle', ($enterprise->category ?? 'Business') . ' Dashboard')

@section('content')

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
    <div class="bg-card border border-border rounded-xl shadow-card p-4 lg:p-6 hover:shadow-card-hover transition-smooth">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-muted-foreground">Total Orders</span>
            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                <i data-lucide="shopping-bag" class="h-4 w-4 text-primary"></i>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold">{{ $stats['total_orders'] }}</p>
        <p class="text-xs text-muted-foreground mt-1">All time</p>
    </div>
    
    <div class="bg-card border border-border rounded-xl shadow-card p-4 lg:p-6 hover:shadow-card-hover transition-smooth">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-muted-foreground">Pending Orders</span>
            <div class="w-8 h-8 bg-warning/10 rounded-lg flex items-center justify-center">
                <i data-lucide="clock" class="h-4 w-4 text-warning"></i>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-warning">{{ $stats['pending_orders'] }}</p>
        <p class="text-xs text-muted-foreground mt-1">Needs attention</p>
    </div>
    
    <div class="bg-card border border-border rounded-xl shadow-card p-4 lg:p-6 hover:shadow-card-hover transition-smooth">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-muted-foreground">In Progress</span>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="loader" class="h-4 w-4 text-blue-500"></i>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-blue-500">{{ $stats['in_progress_orders'] }}</p>
        <p class="text-xs text-muted-foreground mt-1">Active orders</p>
    </div>
    
    <div class="bg-card border border-border rounded-xl shadow-card p-4 lg:p-6 hover:shadow-card-hover transition-smooth">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-muted-foreground">Total Revenue</span>
            <div class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center">
                <i data-lucide="peso-sign" class="h-4 w-4 text-success"></i>
            </div>
        </div>
        <p class="text-2xl lg:text-3xl font-bold text-success">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</p>
        <p class="text-xs text-muted-foreground mt-1">Total earnings</p>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
    <!-- Business Information -->
    <div class="bg-card border border-border rounded-xl shadow-card p-4 lg:p-6 hover:shadow-card-hover transition-smooth">
        <h2 class="text-lg lg:text-xl font-bold mb-4 flex items-center gap-2">
            <div class="w-6 h-6 bg-primary/10 rounded-lg flex items-center justify-center">
                <i data-lucide="info" class="h-4 w-4 text-primary"></i>
            </div>
            Business Information
        </h2>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-muted-foreground">Category</p>
                <p class="font-medium">{{ $enterprise->category ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Address</p>
                <p class="font-medium text-sm">{{ $enterprise->address ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Contact Email</p>
                <p class="font-medium text-sm">{{ $enterprise->email ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Total Services</p>
                <p class="font-medium">{{ $stats['total_services'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="lg:col-span-2 bg-card border border-border rounded-xl shadow-card overflow-hidden hover:shadow-card-hover transition-smooth">
        <div class="p-4 lg:p-6 border-b border-border flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="text-lg lg:text-xl font-bold flex items-center gap-2">
                <div class="w-6 h-6 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="h-4 w-4 text-primary"></i>
                </div>
                Recent Orders
            </h2>
            <a href="{{ route('business.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth">
                <span>View All</span>
                <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        </div>
        
        @if($recent_orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary/50">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Order #</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase hidden sm:table-cell">Customer</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Amount</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Status</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase hidden md:table-cell">Date</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($recent_orders as $order)
                        <tr class="hover:bg-secondary/30 transition-smooth">
                            <td class="px-4 lg:px-6 py-4 font-medium text-sm">#{{ $order->order_no }}</td>
                            <td class="px-4 lg:px-6 py-4 text-sm hidden sm:table-cell">{{ $order->customer_name }}</td>
                            <td class="px-4 lg:px-6 py-4 font-medium text-sm">₱{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 lg:px-6 py-4">
                                @if($order->status_name == 'Pending')
                                    <span class="inline-block px-2 py-1 text-xs bg-warning/10 text-warning rounded-md font-medium">Pending</span>
                                @elseif($order->status_name == 'In Progress')
                                    <span class="inline-block px-2 py-1 text-xs bg-blue-500/10 text-blue-500 rounded-md font-medium">In Progress</span>
                                @elseif(in_array($order->status_name, ['Delivered', 'Shipped'], true))
                                    <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md font-medium">Delivered</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded-md font-medium">{{ $order->status_name ?? 'Unknown' }}</span>
                                @endif
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-sm text-muted-foreground hidden md:table-cell">{{ date('M d', strtotime($order->created_at)) }}</td>
                            <td class="px-4 lg:px-6 py-4">
                                <a href="{{ route('business.orders.details', $order->purchase_order_id) }}" 
                                   class="inline-flex items-center gap-1 text-primary hover:text-primary/80 font-medium text-sm transition-smooth">
                                    <span class="hidden sm:inline">View</span>
                                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 lg:p-12 text-center">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shopping-cart" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">No orders yet</h3>
                <p class="text-muted-foreground text-sm">Orders will appear here once customers place them</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
    lucide.createIcons();
</script>
@endpush
