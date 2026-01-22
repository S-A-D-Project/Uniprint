@extends('layouts.public')

@section('title', 'Customer Dashboard - UniPrint')

@section('content')
<div class="min-h-screen bg-background">
    <!-- Hero Section with Welcome Message -->
    <div class="gradient-primary text-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome back, {{ $user->name }}!</h1>
                    <p class="text-white/80">Your printing dashboard - manage orders and explore services</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pending Orders -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6 hover:shadow-card-hover transition-smooth">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-warning/10 rounded-lg">
                        <i data-lucide="clock" class="h-6 w-6 text-warning"></i>
                    </div>
                    <span class="text-3xl font-bold text-warning">{{ $orderStats['pending'] }}</span>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground mb-1">Pending Orders</h3>
                <p class="text-xs text-muted-foreground">Awaiting confirmation</p>
            </div>

            <!-- In Progress -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6 hover:shadow-card-hover transition-smooth">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-primary/10 rounded-lg">
                        <i data-lucide="loader" class="h-6 w-6 text-primary"></i>
                    </div>
                    <span class="text-3xl font-bold text-primary">{{ $orderStats['in_progress'] }}</span>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground mb-1">In Progress</h3>
                <p class="text-xs text-muted-foreground">Being prepared</p>
            </div>

            <!-- Completed Orders -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6 hover:shadow-card-hover transition-smooth">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-success/10 rounded-lg">
                        <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                    </div>
                    <span class="text-3xl font-bold text-success">{{ $orderStats['completed'] }}</span>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground mb-1">Completed</h3>
                <p class="text-xs text-muted-foreground">Successfully delivered</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - My Orders & Saved Services -->
            <div class="lg:col-span-2 space-y-8">
                <!-- My Orders Section -->
                <div class="bg-card border border-border rounded-xl shadow-card">
                    <div class="p-6 border-b border-border">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold flex items-center gap-2">
                                <i data-lucide="package" class="h-6 w-6 text-primary"></i>
                                My Orders
                            </h2>
                            <a href="{{ route('customer.my-orders') }}" class="text-sm text-primary hover:text-primary/80 font-medium">
                                View All →
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($recentOrders->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentOrders as $order)
                                    <div class="bg-secondary/30 border border-border rounded-lg p-4 hover:shadow-md transition-smooth">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h3 class="font-bold text-lg mb-1">{{ $order->enterprise_name }}</h3>
                                                <p class="text-sm text-muted-foreground">#{{ substr($order->purchase_order_id, 0, 8) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-bold text-primary">₱{{ number_format($order->total, 2) }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium
                                                @if($order->status_name == 'Pending') bg-warning/10 text-warning
                                                @elseif($order->status_name == 'In Progress') bg-primary/10 text-primary
                                                @elseif($order->status_name == 'Delivered') bg-success/10 text-success
                                                @else bg-secondary/10 text-foreground
                                                @endif">
                                                <i data-lucide="circle" class="h-3 w-3"></i>
                                                {{ $order->status_name }}
                                            </span>
                                            <span class="text-sm text-muted-foreground">
                                                {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="mb-4">
                                    <i data-lucide="package" class="h-16 w-16 mx-auto text-muted-foreground"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">No orders yet</h3>
                                <p class="text-muted-foreground mb-6">Start browsing printing services to place your first order</p>
                                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                                    <i data-lucide="store" class="h-4 w-4"></i>
                                    Browse Shops
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Saved Services Section -->
                <div class="bg-card border border-border rounded-xl shadow-card">
                    <div class="p-6 border-b border-border">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold flex items-center gap-2">
                                <i data-lucide="heart" class="h-6 w-6 text-primary"></i>
                                Saved Services
                            </h2>
                            <a href="{{ route('customer.saved-services') }}" class="text-sm text-primary hover:text-primary/80 font-medium">
                                View All →
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($savedServices->count() > 0)
                            <div class="space-y-4">
                                @foreach($savedServices->take(3) as $item)
                                    <div class="bg-secondary/30 border border-border rounded-lg p-4 hover:shadow-md transition-smooth">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="printer" class="h-8 w-8 text-primary"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="font-bold mb-1">{{ $item->service->service_name ?? 'Unknown Service' }}</h3>
                                                <p class="text-sm text-muted-foreground">{{ $item->service->enterprise->name ?? 'Unknown Shop' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-primary">₱{{ number_format($item->total_price, 2) }}</p>
                                                <p class="text-xs text-muted-foreground">Qty: {{ $item->quantity }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="mb-4">
                                    <i data-lucide="heart" class="h-16 w-16 mx-auto text-muted-foreground"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">No saved services yet</h3>
                                <p class="text-muted-foreground mb-6">Services you add to cart will appear here</p>
                                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                                    <i data-lucide="search" class="h-4 w-4"></i>
                                    Explore Services
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions & Profile -->
            <div class="space-y-8">

                <!-- Quick Actions -->
                <div class="bg-card border border-border rounded-xl shadow-card p-6">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <i data-lucide="zap" class="h-5 w-5 text-primary"></i>
                        Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('enterprises.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary transition-smooth">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <i data-lucide="store" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="font-medium">Browse Shops</p>
                                <p class="text-xs text-muted-foreground">Explore printing services</p>
                            </div>
                        </a>
                        <a href="{{ route('ai-design.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary transition-smooth">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <i data-lucide="sparkles" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="font-medium">AI Design Tool</p>
                                <p class="text-xs text-muted-foreground">Create custom designs</p>
                            </div>
                        </a>
                        <a href="{{ route('customer.my-orders') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary transition-smooth">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <i data-lucide="package" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="font-medium">Track Orders</p>
                                <p class="text-xs text-muted-foreground">View order status</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
