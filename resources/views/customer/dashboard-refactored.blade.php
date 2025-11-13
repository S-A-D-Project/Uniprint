@extends('layouts.customer-layout')

@section('title', 'Customer Dashboard')
@section('page_title', 'Customer Dashboard')

@php
    $showPromoBanner = true;
    $showNavigation = true;
@endphp

@section('promo_title', 'Special Offer!')
@section('promo_description', 'Get 20% off on business card printing this week')
@section('promo_cta', 'Shop Now')

@section('navigation_tabs')
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-primary text-primary font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="services" role="tab" aria-selected="true">
    <i data-lucide="shopping-bag" class="h-4 w-4"></i>
    Services Catalog
</button>
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="orders" role="tab" aria-selected="false">
    <i data-lucide="package" class="h-4 w-4"></i>
    Order Management
</button>
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="account" role="tab" aria-selected="false">
    <i data-lucide="user" class="h-4 w-4"></i>
    Account Settings
</button>
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="payments" role="tab" aria-selected="false">
    <i data-lucide="credit-card" class="h-4 w-4"></i>
    Payment History
</button>
<button class="customer-nav-tab tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="support" role="tab" aria-selected="false">
    <i data-lucide="help-circle" class="h-4 w-4"></i>
    Support/Help
</button>
@endsection

@section('content')
<!-- Services Catalog Tab -->
<div id="services-tab" class="tab-content" role="tabpanel">
    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                    <input type="text" id="service-search" placeholder="Search for printing services..." 
                           class="customer-input pl-10">
                </div>
            </div>
            <select id="category-filter" class="customer-input">
                <option value="">All Categories</option>
                <option value="business-cards">Business Cards</option>
                <option value="flyers">Flyers & Brochures</option>
                <option value="posters">Posters & Banners</option>
                <option value="t-shirts">T-Shirts & Apparel</option>
                <option value="documents">Documents & Books</option>
            </select>
            <select id="price-filter" class="customer-input">
                <option value="">All Prices</option>
                <option value="0-100">Under ₱100</option>
                <option value="100-500">₱100 - ₱500</option>
                <option value="500-1000">₱500 - ₱1,000</option>
                <option value="1000+">Above ₱1,000</option>
            </select>
        </div>
    </div>

    <!-- Services Grid -->
    <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Service cards will be loaded here -->
    </div>
</div>

<!-- Order Management Tab -->
<div id="orders-tab" class="tab-content hidden" role="tabpanel">
    <!-- Order Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-bold text-warning">{{ $orderStats['pending'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-warning/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="h-6 w-6 text-warning"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-primary">{{ $orderStats['in_progress'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="package" class="h-6 w-6 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-success">{{ $orderStats['completed'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
        </div>
        <div id="orders-container" class="divide-y divide-gray-200">
            <!-- Orders will be loaded here -->
        </div>
    </div>
</div>

<!-- Account Settings Tab -->
<div id="account-tab" class="tab-content hidden" role="tabpanel">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                </div>
                <form id="profile-form" class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="first_name" value="{{ Auth::user()->first_name }}" class="customer-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="{{ Auth::user()->last_name }}" class="customer-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" class="customer-input">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="customer-button-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Tab -->
<div id="payments-tab" class="tab-content hidden" role="tabpanel">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
        </div>
        <div id="payments-container" class="divide-y divide-gray-200">
            <!-- Payment history will be loaded here -->
        </div>
    </div>
</div>

<!-- Support/Help Tab -->
<div id="support-tab" class="tab-content hidden" role="tabpanel">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">How can we help you?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button class="customer-card p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left">
                        <i data-lucide="package" class="h-6 w-6 text-primary mb-2"></i>
                        <h3 class="font-medium text-gray-900">Order Issues</h3>
                        <p class="text-sm text-gray-600 mt-1">Track orders, returns, refunds</p>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Tab Management
class DashboardTabs {
    constructor() {
        this.activeTab = 'services';
        this.init();
    }

    init() {
        this.setupTabListeners();
        this.loadServices();
    }

    setupTabListeners() {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.currentTarget.dataset.tab;
                this.switchTab(tabName);
            });
        });
    }

    switchTab(tabName) {
        // Update button states
        document.querySelectorAll('.tab-button').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.add('border-primary', 'text-primary');
                btn.classList.remove('border-transparent', 'text-gray-500');
            } else {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });

        // Update content visibility
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`${tabName}-tab`).classList.remove('hidden');
        this.activeTab = tabName;
    }

    async loadServices() {
        try {
            const services = await CustomerLayout.request('/api/customer/services');
            this.renderServices(services);
        } catch (error) {
            console.error('Failed to load services:', error);
        }
    }

    renderServices(services) {
        const container = document.getElementById('services-container');
        container.innerHTML = services.map(service => `
            <div class="customer-card bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="aspect-video bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                    <i data-lucide="printer" class="h-12 w-12 text-primary"></i>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">${service.product_name}</h3>
                    <p class="text-lg font-bold text-primary mb-3">₱${service.base_price}</p>
                    <button onclick="orderService('${service.product_id}')" class="w-full customer-button-primary">
                        Order Now
                    </button>
                </div>
            </div>
        `).join('');
        lucide.createIcons();
    }
}

function orderService(productId) {
    window.location.href = `/products/${productId}`;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardTabs = new DashboardTabs();
});
</script>
@endpush
