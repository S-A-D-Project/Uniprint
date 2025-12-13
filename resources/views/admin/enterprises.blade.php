@extends('layouts.admin-layout')

@section('title', 'Enterprises Management')
@section('page-title', 'Enterprise Overview')
@section('page-subtitle', 'Monitor and manage registered enterprises')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Enterprises', 'url' => '#'],
];
@endphp

@section('header-actions')
    <x-admin.button variant="outline" icon="refresh-cw" size="sm" onclick="location.reload()">
        Refresh Data
    </x-admin.button>
    <x-admin.button variant="outline" icon="download" size="sm">
        Export Report
    </x-admin.button>
    <x-admin.button variant="primary" icon="bar-chart-3" size="sm">
        Analytics
    </x-admin.button>
@endsection

@section('content')
<!-- Enterprise Statistics Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Enterprises -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6 gradient-primary text-white">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-white/70 text-sm mb-1">Total Enterprises</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_enterprises'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="building-2" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="trending-up" class="h-4 w-4"></i>
            <span>Registered businesses</span>
        </div>
    </div>

    <!-- Total Services -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6 gradient-accent text-white">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-white/70 text-sm mb-1">Total Services</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_services'] ?? $stats['total_products'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="package" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="box" class="h-4 w-4"></i>
            <span>Available services</span>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6" style="background: linear-gradient(135deg, hsl(38 92% 50%), hsl(38 95% 60%));">
        <div class="flex justify-between items-start mb-4 text-white">
            <div>
                <p class="text-white/70 text-sm mb-1">Total Orders</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_orders'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="shopping-cart" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="activity" class="h-4 w-4"></i>
            <span>All-time orders</span>
        </div>
    </div>

    <!-- Business Revenue -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6" style="background: linear-gradient(135deg, hsl(142 76% 36%), hsl(142 80% 45%));">
        <div class="flex justify-between items-start mb-4 text-white">
            <div>
                <p class="text-white/70 text-sm mb-1">Business Revenue</p>
                <h3 class="text-3xl font-bold">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="dollar-sign" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="trending-up" class="h-4 w-4"></i>
            <span>Total business earnings</span>
        </div>
    </div>
</div>

<!-- Enterprise Management Table -->
<div class="bg-card border border-border rounded-xl shadow-card overflow-hidden mb-8">
    <div class="p-6 border-b border-border">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="building-2" class="h-5 w-5 text-primary"></i>
                Enterprise Management
            </h3>
            <div class="flex items-center gap-3">
                <div class="text-sm text-muted-foreground">
                    Showing {{ $enterprises->count() }} of {{ $enterprises->total() }} enterprises
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 text-xs bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                        <i data-lucide="filter" class="h-3 w-3 inline mr-1"></i>Filter
                    </button>
                    <button class="px-3 py-1 text-xs bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                        <i data-lucide="search" class="h-3 w-3 inline mr-1"></i>Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        @if(isset($enterprises) && count($enterprises) > 0)
            <table class="w-full">
                <thead class="bg-secondary/50">
                    <tr class="text-left text-sm text-muted-foreground">
                        <th class="p-4">Enterprise</th>
                        <th class="p-4">Contact Info</th>
                        <th class="p-4">Services</th>
                        <th class="p-4">Orders</th>
                        <th class="p-4">Revenue</th>
                        <th class="p-4">Staff</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($enterprises as $enterprise)
                    <tr class="hover:bg-secondary/30 transition-smooth">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="building-2" class="h-5 w-5 text-primary"></i>
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $enterprise->name ?? 'Unknown Enterprise' }}</div>
                                    <div class="text-sm text-muted-foreground">{{ $enterprise->tin_no ?? 'No TIN' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-sm">
                                    <i data-lucide="phone" class="h-3 w-3 text-muted-foreground"></i>
                                    <span>{{ $enterprise->contact_number ?? 'No phone' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <i data-lucide="user" class="h-3 w-3 text-muted-foreground"></i>
                                    <span>{{ $enterprise->contact_person ?? 'No contact person' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">{{ $enterprise->services_count ?? 0 }}</div>
                                <div class="text-xs text-muted-foreground">Services</div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-accent">{{ $enterprise->orders_count ?? 0 }}</div>
                                <div class="text-xs text-muted-foreground">Orders</div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="text-center">
                                <div class="text-lg font-bold text-success">₱{{ number_format($enterprise->total_revenue ?? 0, 0) }}</div>
                                <div class="text-xs text-muted-foreground">Revenue</div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="text-center">
                                <div class="text-xl font-bold text-muted-foreground">{{ $enterprise->staff_count ?? 0 }}</div>
                                <div class="text-xs text-muted-foreground">Staff</div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-success/10 text-success rounded-md font-medium">
                                <i data-lucide="check-circle" class="h-3 w-3"></i>
                                Active
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <button class="p-2 hover:bg-secondary rounded-md transition-colors" title="View Details">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                </button>
                                <button class="p-2 hover:bg-secondary rounded-md transition-colors" title="Manage Services">
                                    <i data-lucide="package" class="h-4 w-4"></i>
                                </button>
                                <button class="p-2 hover:bg-secondary rounded-md transition-colors" title="View Orders">
                                    <i data-lucide="shopping-cart" class="h-4 w-4"></i>
                                </button>
                                <button class="p-2 hover:bg-secondary rounded-md transition-colors" title="Settings">
                                    <i data-lucide="settings" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="building-2" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">No enterprises found</h3>
                <p class="text-muted-foreground mb-4">No enterprises have been registered yet</p>
            </div>
        @endif
    </div>

    @if($enterprises->hasPages())
    <div class="p-6 border-t border-border">
        <div class="flex justify-center">
            {{ $enterprises->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Recent Activity -->
@if(isset($recent_orders) && count($recent_orders) > 0)
<div class="bg-card border border-border rounded-xl shadow-card overflow-hidden">
    <div class="p-6 border-b border-border">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <i data-lucide="activity" class="h-5 w-5 text-primary"></i>
            Recent Enterprise Activity
        </h3>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            @foreach($recent_orders->take(5) as $order)
            <div class="flex items-center gap-4 p-4 bg-secondary/20 rounded-lg">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="h-5 w-5 text-primary"></i>
                </div>
                <div class="flex-1">
                    <div class="font-medium">{{ $order->enterprise_name ?? 'Unknown Enterprise' }}</div>
                    <div class="text-sm text-muted-foreground">
                        New order from {{ $order->customer_name ?? 'Unknown Customer' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold">₱{{ number_format($order->total_order_amount ?? 0, 2) }}</div>
                    <div class="text-xs text-muted-foreground">
                        {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y', strtotime($order->created_at)) : $order->created_at->format('M d, Y')) : 'Unknown date' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Enterprise management functionality
class EnterpriseManager {
    constructor() {
        this.init();
    }

    init() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Setup event listeners
        this.setupEventListeners();
        
        // Auto-refresh statistics every 60 seconds
        this.startAutoRefresh();
    }

    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.querySelector('[onclick="location.reload()"]');
        if (refreshBtn) {
            refreshBtn.removeAttribute('onclick');
            refreshBtn.addEventListener('click', () => this.refreshData());
        }

        // Action buttons
        document.querySelectorAll('[title="View Details"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const enterpriseName = row.querySelector('.font-semibold').textContent;
                this.viewEnterpriseDetails(enterpriseName);
            });
        });

        document.querySelectorAll('[title="Manage Services"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const enterpriseName = row.querySelector('.font-semibold').textContent;
                this.manageServices(enterpriseName);
            });
        });

        document.querySelectorAll('[title="View Orders"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const enterpriseName = row.querySelector('.font-semibold').textContent;
                this.viewOrders(enterpriseName);
            });
        });
    }

    async refreshData() {
        const refreshBtn = document.querySelector('button[onclick*="reload"]') || 
                          document.querySelector('x-admin\\.button[icon="refresh-cw"]');
        
        try {
            if (refreshBtn) {
                refreshBtn.disabled = true;
                const originalContent = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin mr-2"></i>Refreshing...';
                lucide.createIcons();
            }

            // Simulate API call - in real implementation, this would fetch fresh data
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            this.showNotification('Enterprise data refreshed successfully', 'success');
            
            // Reload page to get fresh data
            setTimeout(() => {
                window.location.reload();
            }, 500);

        } catch (error) {
            console.error('Refresh error:', error);
            this.showNotification('Failed to refresh data', 'error');
        }
    }

    viewEnterpriseDetails(enterpriseName) {
        this.showNotification(`Viewing details for ${enterpriseName}`, 'info');
        // In real implementation, this would open a modal or navigate to details page
    }

    manageServices(enterpriseName) {
        this.showNotification(`Managing services for ${enterpriseName}`, 'info');
        // In real implementation, this would navigate to services management
    }

    viewOrders(enterpriseName) {
        this.showNotification(`Viewing orders for ${enterpriseName}`, 'info');
        // In real implementation, this would filter orders by enterprise
    }

    startAutoRefresh() {
        // Auto-refresh every 5 minutes
        setInterval(() => {
            this.updateStatistics();
        }, 300000);
    }

    async updateStatistics() {
        try {
            // In real implementation, this would fetch updated statistics
            console.log('Updating enterprise statistics...');
        } catch (error) {
            console.error('Statistics update error:', error);
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
        
        switch (type) {
            case 'success':
                notification.classList.add('bg-green-500', 'text-white');
                break;
            case 'error':
                notification.classList.add('bg-red-500', 'text-white');
                break;
            default:
                notification.classList.add('bg-blue-500', 'text-white');
        }

        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" class="h-5 w-5"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);
        lucide.createIcons();

        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.enterpriseManager = new EnterpriseManager();
});
</script>
@endpush
