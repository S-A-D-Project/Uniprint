@extends('layouts.admin-layout')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Overview')
@section('page-subtitle', 'Monitor and manage your UniPrint platform')

@php
$breadcrumbs = [
    ['label' => 'Home', 'url' => route('admin.dashboard')],
    ['label' => 'Dashboard', 'url' => '#'],
];
@endphp

@section('header-actions')
    <x-admin.button variant="outline" icon="refresh-cw" size="sm">
        Refresh Data
    </x-admin.button>
    <x-admin.button variant="primary" icon="download" size="sm">
        Export Report
    </x-admin.button>
@endsection

@section('content')

<!-- Alert Example -->
@if(session('welcome'))
    <x-admin.alert type="success" :dismissible="true" class="mb-6">
        Welcome to the new admin dashboard! All systems are operational.
    </x-admin.alert>
@endif

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-admin.stat-card 
        title="Total Users" 
        value="{{ $stats['total_users'] ?? 0 }}" 
        icon="users"
        color="primary"
        trend="+12% from last month"
        trendType="up" />
        
    <x-admin.stat-card 
        title="Total Orders" 
        value="{{ $stats['total_orders'] ?? 0 }}" 
        icon="shopping-cart"
        color="accent"
        trend="+8% from last week"
        trendType="up" />
        
    <x-admin.stat-card 
        title="Pending Orders" 
        value="{{ $stats['pending_orders'] ?? 0 }}" 
        icon="clock"
        color="warning"
        trend="Needs attention"
        trendType="neutral" />
        
    <x-admin.stat-card 
        title="Total Revenue" 
        value="₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}" 
        icon="dollar-sign"
        color="success"
        trend="+15% from last month"
        trendType="up" />
</div>

<!-- Secondary Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-admin.card icon="user-check" hover>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-muted-foreground mb-1">Customers</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_customers'] ?? 0 }}</h3>
            </div>
            <div class="bg-primary/10 p-3 rounded-lg">
                <i data-lucide="user-check" class="h-6 w-6 text-primary"></i>
            </div>
        </div>
    </x-admin.card>
    
    <x-admin.card icon="briefcase" hover>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-muted-foreground mb-1">Business Users</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_business_users'] ?? 0 }}</h3>
            </div>
            <div class="bg-accent/10 p-3 rounded-lg">
                <i data-lucide="briefcase" class="h-6 w-6 text-accent"></i>
            </div>
        </div>
    </x-admin.card>
    
    <x-admin.card icon="building-2" hover>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-muted-foreground mb-1">Active Enterprises</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_enterprises'] ?? 0 }}</h3>
            </div>
            <div class="bg-success/10 p-3 rounded-lg">
                <i data-lucide="building-2" class="h-6 w-6 text-success"></i>
            </div>
        </div>
    </x-admin.card>
</div>

<!-- Tabbed Section: Users and Orders -->
@php
$managementTabs = [
    ['label' => 'Users', 'icon' => 'users'],
    ['label' => 'Orders', 'icon' => 'shopping-cart'],
];
@endphp

<x-admin.card title="Quick Management" icon="database" class="mb-8">
    <x-slot:actions>
        <x-admin.button size="sm" variant="ghost" icon="external-link">
            View All
        </x-admin.button>
    </x-slot:actions>
    
    <x-admin.tabs :tabs="$managementTabs" id="management-tabs">
        <!-- Users Tab -->
        <x-admin.tab-panel tabset="management-tabs" :index="0" :active="true">
            <div class="admin-table-responsive">
                @if(isset($users) && count($users) > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Enterprise</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users->take(10) as $user)
                            <tr>
                                <td class="font-semibold">{{ $user->user_id ?? 'N/A' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="user" class="h-4 w-4 text-muted-foreground"></i>
                                        {{ $user->username ?? $user->name ?? 'Unknown' }}
                                    </div>
                                </td>
                                <td>{{ $user->email ?? 'N/A' }}</td>
                                <td>
                                    @if(($user->role_type ?? '') == 'admin')
                                        <x-admin.badge variant="destructive">Admin</x-admin.badge>
                                    @elseif(($user->role_type ?? '') == 'business_user')
                                        <x-admin.badge variant="primary">Business User</x-admin.badge>
                                    @else
                                        <x-admin.badge variant="success">Customer</x-admin.badge>
                                    @endif
                                </td>
                                <td>
                                    @if(($user->is_active ?? true))
                                        <x-admin.badge variant="success" icon="check-circle">Active</x-admin.badge>
                                    @else
                                        <x-admin.badge variant="secondary" icon="x-circle">Inactive</x-admin.badge>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($user->enterprise_name))
                                        {{ $user->enterprise_name }}
                                    @else
                                        <span class="text-muted-foreground">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-muted-foreground">
                                    {{ isset($user->created_at) ? (is_string($user->created_at) ? date('M d, Y', strtotime($user->created_at)) : $user->created_at->format('M d, Y')) : 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($users->hasPages())
                    <div class="mt-4 flex justify-center">
                        <x-admin.button href="{{ route('admin.users') }}" variant="outline" icon="arrow-right" iconPosition="right">
                            View All Users
                        </x-admin.button>
                    </div>
                    @endif
                @else
                    <x-admin.empty-state 
                        icon="users"
                        title="No users found"
                        description="No users have been registered yet" />
                @endif
            </div>
        </x-admin.tab-panel>
        
        <!-- Orders Tab -->
        <x-admin.tab-panel tabset="management-tabs" :index="1">
            <div class="admin-table-responsive">
                @if(isset($orders) && count($orders) > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Enterprise</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders->take(10) as $order)
                            <tr>
                                <td>
                                    <span class="font-mono text-sm font-medium">
                                        #{{ $order->order_no ?? ($order->purchase_order_id ? substr($order->purchase_order_id, 0, 8) : 'N/A') }}
                                    </span>
                                </td>
                                <td>{{ $order->customer_name ?? 'Unknown Customer' }}</td>
                                <td>{{ $order->enterprise_name ?? 'Unknown Enterprise' }}</td>
                                <td>
                                    <x-admin.badge variant="secondary">
                                        Order Items
                                    </x-admin.badge>
                                </td>
                                <td class="font-medium">₱{{ number_format($order->total ?? $order->total_order_amount ?? 0, 2) }}</td>
                                <td>
                                    @if(($order->status_name ?? '') == 'Pending')
                                        <x-admin.badge variant="warning" icon="clock">Pending</x-admin.badge>
                                    @elseif(($order->status_name ?? '') == 'In Progress')
                                        <x-admin.badge variant="info" icon="loader">In Progress</x-admin.badge>
                                    @elseif(($order->status_name ?? '') == 'Shipped')
                                        <x-admin.badge variant="primary" icon="truck">Shipped</x-admin.badge>
                                    @elseif(($order->status_name ?? '') == 'Delivered')
                                        <x-admin.badge variant="success" icon="check-circle">Delivered</x-admin.badge>
                                    @else
                                        <x-admin.badge variant="secondary">{{ $order->status_name ?? 'Unknown' }}</x-admin.badge>
                                    @endif
                                </td>
                                <td class="text-sm text-muted-foreground">
                                    {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i')) : 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($orders->hasPages())
                    <div class="mt-4 flex justify-center">
                        <x-admin.button href="{{ route('admin.orders') }}" variant="outline" icon="arrow-right" iconPosition="right">
                            View All Orders
                        </x-admin.button>
                    </div>
                    @endif
                @else
                    <x-admin.empty-state 
                        icon="shopping-cart"
                        title="No orders found"
                        description="Orders will appear here once customers place them" />
                @endif
            </div>
        </x-admin.tab-panel>
    </x-admin.tabs>
</x-admin.card>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Quick Actions -->
    <x-admin.card title="Quick Actions" icon="zap">
        <div class="space-y-3">
            <x-admin.button href="{{ route('admin.users') }}" variant="outline" icon="users" class="w-full justify-start">
                Manage Users
            </x-admin.button>
            <x-admin.button href="{{ route('admin.orders') }}" variant="outline" icon="shopping-cart" class="w-full justify-start">
                View Orders
            </x-admin.button>
            <x-admin.button href="{{ route('admin.services') }}" variant="outline" icon="package" class="w-full justify-start">
                Manage Services
            </x-admin.button>
            <x-admin.button href="{{ route('admin.reports') }}" variant="outline" icon="bar-chart" class="w-full justify-start">
                Generate Reports
            </x-admin.button>
        </div>
    </x-admin.card>
    
    <!-- System Status -->
    <x-admin.card title="System Status" icon="activity">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Database</span>
                </div>
                <x-admin.badge variant="success">Online</x-admin.badge>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Application</span>
                </div>
                <x-admin.badge variant="success">Healthy</x-admin.badge>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Storage</span>
                </div>
                <x-admin.badge variant="success">75% Available</x-admin.badge>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-warning rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Cache</span>
                </div>
                <x-admin.badge variant="warning">Needs Clearing</x-admin.badge>
            </div>
        </div>
    </x-admin.card>
</div>

@endsection

@push('scripts')
<script>
    // Ensure Lucide icons are initialized after dynamic content loads
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush
