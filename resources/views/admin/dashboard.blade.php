@extends('layouts.admin-layout')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Overview')
@section('page-subtitle', 'Monitor and manage your UniPrint platform')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '#'],
];
@endphp

@section('header-actions')
    <div class="flex items-center gap-2">
        <div id="last-updated" class="text-sm text-muted-foreground">
            Last updated: <span id="update-time">{{ now()->format('H:i:s') }}</span>
        </div>
        <x-admin.button variant="outline" icon="settings" size="sm" href="{{ route('admin.settings') }}">
            Settings
        </x-admin.button>
        <x-admin.button variant="primary" icon="refresh-cw" size="sm" id="refresh-dashboard">
            Refresh Data
        </x-admin.button>
    </div>
@endsection

@section('content')

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6 gradient-primary text-white">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-white/70 text-sm mb-1">Total Users</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_users'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="users" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="trending-up" class="h-4 w-4"></i>
            <span>All registered users</span>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6 gradient-accent text-white">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-white/70 text-sm mb-1">Total Orders</p>
                <h3 class="text-3xl font-bold">{{ $stats['total_orders'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="shopping-cart" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="package" class="h-4 w-4"></i>
            <span>Lifetime orders</span>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6" style="background: linear-gradient(135deg, hsl(38 92% 50%), hsl(38 95% 60%));">
        <div class="flex justify-between items-start mb-4 text-white">
            <div>
                <p class="text-white/70 text-sm mb-1">Pending Orders</p>
                <h3 class="text-3xl font-bold">{{ $stats['pending_orders'] }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="clock" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="alert-circle" class="h-4 w-4"></i>
            <span>Needs attention</span>
        </div>
    </div>

    <!-- Admin Commission -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6" style="background: linear-gradient(135deg, hsl(142 76% 36%), hsl(142 80% 45%));">
        <div class="flex justify-between items-start mb-4 text-white">
            <div>
                <p class="text-white/70 text-sm mb-1">Admin Commission</p>
                <h3 class="text-3xl font-bold">₱{{ number_format($stats['admin_commission'] ?? 0, 2) }}</h3>
            </div>
            <div class="bg-white/20 p-3 rounded-lg">
                <i data-lucide="percent" class="h-6 w-6"></i>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm text-white/80">
            <i data-lucide="trending-up" class="h-4 w-4"></i>
            <span>{{ $stats['commission_rate'] ?? 5 }}% from orders</span>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="bg-primary/10 p-2 rounded-lg">
                <i data-lucide="user-check" class="h-5 w-5 text-primary"></i>
            </div>
            <h4 class="text-lg font-semibold">Customers</h4>
        </div>
        <p class="text-3xl font-bold">{{ $stats['total_customers'] }}</p>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="bg-accent/10 p-2 rounded-lg">
                <i data-lucide="briefcase" class="h-5 w-5 text-accent"></i>
            </div>
            <h4 class="text-lg font-semibold">Business Users</h4>
        </div>
        <p class="text-3xl font-bold">{{ $stats['total_business_users'] }}</p>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="bg-success/10 p-2 rounded-lg">
                <i data-lucide="building-2" class="h-5 w-5 text-success"></i>
            </div>
            <h4 class="text-lg font-semibold">Active Enterprises</h4>
        </div>
        <p class="text-3xl font-bold">{{ $stats['total_enterprises'] }}</p>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="bg-blue-500/10 p-2 rounded-lg">
                <i data-lucide="banknote" class="h-5 w-5 text-blue-500"></i>
            </div>
            <h4 class="text-lg font-semibold">Total Order Value</h4>
        </div>
        <p class="text-2xl font-bold">₱{{ number_format($stats['total_order_value'] ?? 0, 2) }}</p>
        <p class="text-xs text-muted-foreground mt-1">Paid to businesses</p>
    </div>
</div>

<!-- Tabbed Users and Orders Section -->
<div class="bg-card border border-border rounded-xl shadow-card overflow-hidden mb-8">
    <div class="p-6 border-b border-border">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="database" class="h-5 w-5 text-primary"></i>
                Quick Management
            </h3>
            <div class="flex gap-2">
                <button onclick="switchTab('users')" id="users-tab" class="px-4 py-2 text-sm font-medium rounded-md bg-primary text-white transition-smooth">
                    <i data-lucide="users" class="h-4 w-4 inline mr-1"></i>Users
                </button>
                <button onclick="switchTab('orders')" id="orders-tab" class="px-4 py-2 text-sm font-medium rounded-md bg-secondary text-secondary-foreground hover:bg-secondary/80 transition-smooth">
                    <i data-lucide="shopping-cart" class="h-4 w-4 inline mr-1"></i>Orders
                </button>
            </div>
        </div>
    </div>

    <!-- Users Tab Content -->
    <div id="users-content" class="p-6">
        <div class="overflow-x-auto">
            @if(isset($users) && count($users) > 0)
                <table class="w-full">
                    <thead class="bg-secondary/50">
                        <tr class="text-left text-sm text-muted-foreground">
                            <th class="p-4">ID</th>
                            <th class="p-4">Username</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Enterprise</th>
                            <th class="p-4">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($users->take(10) as $user)
                        <tr class="hover:bg-secondary/30 transition-smooth">
                            <td class="p-4"><strong>{{ $user->user_id ?? 'N/A' }}</strong></td>
                            <td class="p-4">
                                <i class="bi bi-person-circle me-2"></i>
                                {{ $user->username ?? $user->name ?? 'Unknown' }}
                            </td>
                            <td class="p-4">{{ $user->email ?? 'N/A' }}</td>
                            <td class="p-4">
                                @if(($user->role_type ?? '') == 'admin')
                                    <span class="inline-block px-2 py-1 text-xs bg-danger/10 text-danger rounded-md font-medium">Admin</span>
                                @elseif(($user->role_type ?? '') == 'business_user')
                                    <span class="inline-block px-2 py-1 text-xs bg-primary/10 text-primary rounded-md font-medium">Business User</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md font-medium">Customer</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if(($user->is_active ?? true))
                                    <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md font-medium">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded-md font-medium">
                                        <i class="bi bi-x-circle"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if(!empty($user->enterprise_name))
                                    {{ $user->enterprise_name }}
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-sm text-muted-foreground">
                                {{ isset($user->created_at) ? (is_string($user->created_at) ? date('M d, Y', strtotime($user->created_at)) : $user->created_at->format('M d, Y')) : 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($users->hasPages())
                <div class="mt-4 flex justify-center">
                    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded-md hover:bg-primary/90 transition-smooth">
                        View All Users
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <i data-lucide="users" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                    <h3 class="text-lg font-semibold mb-2">No users found</h3>
                    <p class="text-muted-foreground">No users have been registered yet</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Orders Tab Content -->
    <div id="orders-content" class="p-6 hidden">
        <div class="overflow-x-auto">
            @if(isset($orders) && count($orders) > 0)
                <table class="w-full">
                    <thead class="bg-secondary/50">
                        <tr class="text-left text-sm text-muted-foreground">
                            <th class="p-4">Order #</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Enterprise</th>
                            <th class="p-4">Items</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($orders->take(10) as $order)
                        <tr class="hover:bg-secondary/30 transition-smooth">
                            <td class="p-4">
                                <span class="font-mono text-sm font-medium">#{{ $order->order_no ?? ($order->purchase_order_id ? substr($order->purchase_order_id, 0, 8) : 'N/A') }}</span>
                            </td>
                            <td class="p-4">{{ $order->customer_name ?? 'Unknown Customer' }}</td>
                            <td class="p-4">{{ $order->enterprise_name ?? 'Unknown Enterprise' }}</td>
                            <td class="p-4">
                                <span class="inline-block px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded-md">
                                    Order Items
                                </span>
                            </td>
                            <td class="p-4 font-medium">₱{{ number_format($order->total ?? $order->total_order_amount ?? 0, 2) }}</td>
                            <td class="p-4">
                                @if(($order->status_name ?? '') == 'Pending')
                                    <span class="inline-block px-2 py-1 text-xs bg-warning/10 text-warning rounded-md font-medium">
                                        <i data-lucide="clock" class="h-3 w-3 inline mr-1"></i> Pending
                                    </span>
                                @elseif(($order->status_name ?? '') == 'In Progress')
                                    <span class="inline-block px-2 py-1 text-xs bg-blue-500/10 text-blue-500 rounded-md font-medium">
                                        <i data-lucide="loader" class="h-3 w-3 inline mr-1"></i> In Progress
                                    </span>
                                @elseif(in_array(($order->status_name ?? ''), ['Delivered', 'Shipped'], true))
                                    <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md font-medium">
                                        <i data-lucide="check-circle" class="h-3 w-3 inline mr-1"></i> Delivered
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded-md font-medium">
                                        {{ $order->status_name ?? 'Unknown' }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-sm text-muted-foreground">
                                {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i')) : 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($orders->hasPages())
                <div class="mt-4 flex justify-center">
                    <a href="{{ route('admin.orders') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-primary text-white rounded-md hover:bg-primary/90 transition-smooth">
                        View All Orders
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <i data-lucide="shopping-cart" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                    <h3 class="text-lg font-semibold mb-2">No orders found</h3>
                    <p class="text-muted-foreground">Orders will appear here once customers place them</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Real-time dashboard functionality
class AdminDashboard {
    constructor() {
        this.refreshInterval = null;
        this.autoRefreshEnabled = false;
        this.init();
    }

    init() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Setup event listeners
        this.setupEventListeners();
        
        // Start auto-refresh if enabled
        this.startAutoRefresh();
    }

    setupEventListeners() {
        // Manual refresh button
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshDashboard());
        }

        // Auto-refresh toggle (if implemented)
        const autoRefreshToggle = document.getElementById('auto-refresh-toggle');
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                this.autoRefreshEnabled = e.target.checked;
                if (this.autoRefreshEnabled) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            });
        }
    }

    async refreshDashboard() {
        const refreshBtn = document.getElementById('refresh-dashboard');
        const originalText = refreshBtn?.innerHTML;
        
        try {
            // Show loading state
            if (refreshBtn) {
                if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
                    UniPrintUI.setButtonLoading(refreshBtn, true, { text: 'Refreshing...' });
                } else {
                    refreshBtn.disabled = true;
                    refreshBtn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin mr-2"></i>Refreshing...';
                    lucide.createIcons();
                }
            }

            // Fetch fresh data
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update timestamp
                this.updateTimestamp();
                
                // Show success feedback
                this.showNotification('Dashboard data refreshed successfully', 'success');
                
                // Optionally reload the page for full refresh
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                throw new Error('Failed to refresh data');
            }

        } catch (error) {
            console.error('Dashboard refresh error:', error);
            this.showNotification('Failed to refresh dashboard data', 'error');
        } finally {
            // Restore button state
            if (refreshBtn) {
                if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
                    UniPrintUI.setButtonLoading(refreshBtn, false);
                } else {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalText;
                    lucide.createIcons();
                }
            }
        }
    }

    updateTimestamp() {
        const timeElement = document.getElementById('update-time');
        if (timeElement) {
            const now = new Date();
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }

    startAutoRefresh() {
        if (this.autoRefreshEnabled && !this.refreshInterval) {
            this.refreshInterval = setInterval(() => {
                this.refreshDashboard();
            }, 30000); // Refresh every 30 seconds
        }
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
        
        // Set notification style based on type
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

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Tab switching functionality
function switchTab(tabName) {
    // Hide all content
    document.getElementById('users-content').classList.add('hidden');
    document.getElementById('orders-content').classList.add('hidden');
    
    // Remove active styles from all tabs
    document.getElementById('users-tab').classList.remove('bg-primary', 'text-white');
    document.getElementById('users-tab').classList.add('bg-secondary', 'text-secondary-foreground', 'hover:bg-secondary/80');
    document.getElementById('orders-tab').classList.remove('bg-primary', 'text-white');
    document.getElementById('orders-tab').classList.add('bg-secondary', 'text-secondary-foreground', 'hover:bg-secondary/80');
    
    // Show selected content and activate tab
    if (tabName === 'users') {
        document.getElementById('users-content').classList.remove('hidden');
        document.getElementById('users-tab').classList.remove('bg-secondary', 'text-secondary-foreground', 'hover:bg-secondary/80');
        document.getElementById('users-tab').classList.add('bg-primary', 'text-white');
    } else if (tabName === 'orders') {
        document.getElementById('orders-content').classList.remove('hidden');
        document.getElementById('orders-tab').classList.remove('bg-secondary', 'text-secondary-foreground', 'hover:bg-secondary/80');
        document.getElementById('orders-tab').classList.add('bg-primary', 'text-white');
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.adminDashboard = new AdminDashboard();
});

// Update timestamp every second
setInterval(() => {
    if (window.adminDashboard) {
        window.adminDashboard.updateTimestamp();
    }
}, 1000);
</script>
@endpush
