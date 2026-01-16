@extends('layouts.customer-layout')

@section('title', 'Services Catalog - UniPrint')
@section('page_title', 'Services Catalog')

@php
    $showNavigation = true;
@endphp

@section('navigation_tabs')
<a href="{{ route('customer.dashboard') }}" class="py-4 px-6 border-b-2 border-primary text-primary font-medium text-sm whitespace-nowrap flex items-center gap-2">
    <i data-lucide="shopping-bag" class="h-4 w-4"></i>
    Services Catalog
</a>
<a href="{{ route('customer.orders') }}" class="py-4 px-6 border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="package" class="h-4 w-4"></i>
    My Orders
</a>
<a href="{{ route('ai-design.index') }}" class="py-4 px-6 border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="sparkles" class="h-4 w-4"></i>
    AI Design
</a>
<a href="{{ route('profile.index') }}" class="py-4 px-6 border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
    <i data-lucide="user" class="h-4 w-4"></i>
    Account Settings
</a>
@endsection

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Printing Shops in Baguio</h1>
    <p class="text-gray-600 text-lg">Find the perfect printing shop for your needs with AI-enhanced ordering</p>
</div>

<!-- Search Bar -->
<div class="mb-8">
    <div class="relative max-w-2xl">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
        </div>
        <input type="text" 
               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary" 
               placeholder="Search for printing shops..." 
               id="shop-search">
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-8">
    <div class="flex flex-wrap gap-2">
        <button class="px-4 py-2 bg-primary text-white rounded-full font-medium text-sm hover:bg-primary/90 transition-colors" data-filter="all">
            All
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors" data-filter="print-shop">
            Print Shop
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors" data-filter="t-shirt">
            T-Shirt Printing
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors" data-filter="large-format">
            Large Format
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors" data-filter="business-cards">
            Business Cards
        </button>
    </div>
</div>
    </header>

    <!-- Promotional Banner -->
    <div class="bg-gradient-to-r from-primary to-primary/80 text-white">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="tag" class="h-6 w-6"></i>
                    <div>
                        <h2 class="font-bold text-lg">Special Offer!</h2>
                        <p class="text-sm opacity-90">Get 20% off on business card printing this week</p>
                    </div>
                </div>
                <button class="bg-white text-primary px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                    Shop Now
                </button>
            </div>
        </div>
    </div>


    <!-- Tab Content -->
    <main class="container mx-auto px-4 py-6">
        <!-- Services Catalog Tab -->
        <div id="services-tab" class="tab-content" role="tabpanel">
            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Search Bar -->
                    <div class="flex-1">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"></i>
                            <input type="text" 
                                   id="service-search"
                                   placeholder="Search for printing services..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Categories</option>
                        <option value="business-cards">Business Cards</option>
                        <option value="flyers">Flyers & Brochures</option>
                        <option value="posters">Posters & Banners</option>
                        <option value="t-shirts">T-Shirts & Apparel</option>
                        <option value="documents">Documents & Books</option>
                    </select>

                    <!-- Price Range -->
                    <select id="price-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Prices</option>
                        <option value="0-100">Under ₱100</option>
                        <option value="100-500">₱100 - ₱500</option>
                        <option value="500-1000">₱500 - ₱1,000</option>
                        <option value="1000+">Above ₱1,000</option>
                    </select>

                    <!-- Sort By -->
                    <select id="sort-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="popular">Most Popular</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>
            </div>

            <!-- Services Grid -->
            <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Service cards will be loaded here -->
            </div>

            <!-- Load More -->
            <div class="text-center mt-8">
                <button id="load-more-services" class="px-6 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Load More Services
                </button>
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
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                        <a href="{{ route('customer.orders') }}" class="text-primary hover:text-primary/80 text-sm font-medium">
                            View All Orders
                        </a>
                    </div>
                </div>
                <div id="orders-container" class="divide-y divide-gray-200">
                    <!-- Orders will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Account Settings Tab -->
        <div id="account-tab" class="tab-content hidden" role="tabpanel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                        </div>
                        <form id="profile-form" class="p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                    <input type="text" name="first_name" value="{{ $user->first_name }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                    <input type="text" name="last_name" value="{{ $user->last_name }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" value="{{ $user->email }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone_number" value="{{ $user->phone_number ?? '' }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ $user->address ?? '' }}</textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="key" class="h-4 w-4"></i>
                                Change Password
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="bell" class="h-4 w-4"></i>
                                Notification Settings
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                Download My Data
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                Delete Account
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Preferences</h3>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Email Notifications</span>
                                <input type="checkbox" checked class="rounded text-primary focus:ring-primary">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">SMS Updates</span>
                                <input type="checkbox" class="rounded text-primary focus:ring-primary">
                            </label>
                            <label class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Marketing Emails</span>
                                <input type="checkbox" class="rounded text-primary focus:ring-primary">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Tab -->
        <div id="payments-tab" class="tab-content hidden" role="tabpanel">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
                        <div class="flex items-center gap-2">
                            <button class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900">
                                <i data-lucide="download" class="h-4 w-4 inline mr-1"></i>
                                Export
                            </button>
                            <select class="px-3 py-1 text-sm border border-gray-300 rounded-lg">
                                <option>Last 30 days</option>
                                <option>Last 3 months</option>
                                <option>Last 6 months</option>
                                <option>All time</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="payments-container" class="divide-y divide-gray-200">
                    <!-- Payment history will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Support/Help Tab -->
        <div id="support-tab" class="tab-content hidden" role="tabpanel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Help Categories -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">How can we help you?</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button class="p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left">
                                <i data-lucide="package" class="h-6 w-6 text-primary mb-2"></i>
                                <h3 class="font-medium text-gray-900">Order Issues</h3>
                                <p class="text-sm text-gray-600 mt-1">Track orders, returns, refunds</p>
                            </button>
                            <button class="p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left">
                                <i data-lucide="credit-card" class="h-6 w-6 text-primary mb-2"></i>
                                <h3 class="font-medium text-gray-900">Payment & Billing</h3>
                                <p class="text-sm text-gray-600 mt-1">Invoices, payment methods</p>
                            </button>
                            <button class="p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left">
                                <i data-lucide="printer" class="h-6 w-6 text-primary mb-2"></i>
                                <h3 class="font-medium text-gray-900">Printing Services</h3>
                                <p class="text-sm text-gray-600 mt-1">Service options, pricing</p>
                            </button>
                            <button class="p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-all text-left">
                                <i data-lucide="user" class="h-6 w-6 text-primary mb-2"></i>
                                <h3 class="font-medium text-gray-900">Account Help</h3>
                                <p class="text-sm text-gray-600 mt-1">Login, profile, settings</p>
                            </button>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Frequently Asked Questions</h3>
                        <div class="space-y-3">
                            <details class="border border-gray-200 rounded-lg">
                                <summary class="p-4 cursor-pointer hover:bg-gray-50 font-medium text-gray-900">
                                    How long does printing take?
                                </summary>
                                <div class="p-4 pt-0 text-sm text-gray-600">
                                    Standard printing takes 3-5 business days. Express options are available for 24-48 hour delivery.
                                </div>
                            </details>
                            <details class="border border-gray-200 rounded-lg">
                                <summary class="p-4 cursor-pointer hover:bg-gray-50 font-medium text-gray-900">
                                    What payment methods do you accept?
                                </summary>
                                <div class="p-4 pt-0 text-sm text-gray-600">
                                    We accept credit/debit cards, GCash, bank transfers, and cash on delivery.
                                </div>
                            </details>
                            <details class="border border-gray-200 rounded-lg">
                                <summary class="p-4 cursor-pointer hover:bg-gray-50 font-medium text-gray-900">
                                    Can I track my order?
                                </summary>
                                <div class="p-4 pt-0 text-sm text-gray-600">
                                    Yes, you can track your order in real-time through your dashboard.
                                </div>
                            </details>
                        </div>
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-medium text-gray-900 mb-4">Contact Support</h3>
                        <div class="space-y-3">
                            <a href="tel:+63212345678" class="flex items-center gap-3 p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <i data-lucide="phone" class="h-5 w-5 text-primary"></i>
                                <div>
                                    <p class="font-medium">Phone Support</p>
                                    <p class="text-sm text-gray-600">(02) 123-45678</p>
                                </div>
                            </a>
                            <a href="mailto:support@uniprint.com" class="flex items-center gap-3 p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <i data-lucide="mail" class="h-5 w-5 text-primary"></i>
                                <div>
                                    <p class="font-medium">Email Support</p>
                                    <p class="text-sm text-gray-600">support@uniprint.com</p>
                                </div>
                            </a>
                            <button class="flex items-center gap-3 p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors w-full text-left">
                                <i data-lucide="message-circle" class="h-5 w-5 text-primary"></i>
                                <div>
                                    <p class="font-medium">Live Chat</p>
                                    <p class="text-sm text-gray-600">Available 9 AM - 6 PM</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-primary to-primary/80 text-white rounded-lg p-6">
                        <h3 class="font-medium mb-2">Need urgent help?</h3>
                        <p class="text-sm opacity-90 mb-4">Our support team is ready to assist you with any issues.</p>
                        <button class="w-full bg-white text-primary px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                            Start Live Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

@push('styles')
<style>
/* Tab animations */
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Service card hover effects */
.service-card {
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .tab-button {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }
    
    .tab-button i {
        height: 1rem;
        width: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Tab Management System
class TabManager {
    constructor() {
        this.activeTab = 'services';
        this.tabData = new Map();
        this.init();
    }

    init() {
        this.setupTabListeners();
        this.loadInitialTab();
        this.setupSearchAndFilters();
        this.setupProfileForm();
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
        if (this.activeTab === tabName) return;

        // Update button states
        document.querySelectorAll('.tab-button').forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.add('border-primary', 'text-primary');
                btn.classList.remove('border-transparent', 'text-gray-500');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
                btn.setAttribute('aria-selected', 'false');
            }
        });

        // Update content visibility
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`${tabName}-tab`).classList.remove('hidden');

        // Load tab data if not cached
        if (!this.tabData.has(tabName)) {
            this.loadTabData(tabName);
        }

        this.activeTab = tabName;
        this.saveTabPreference(tabName);
    }

    async loadTabData(tabName) {
        try {
            switch(tabName) {
                case 'services':
                    await this.loadServices();
                    break;
                case 'orders':
                    await this.loadOrders();
                    break;
                case 'payments':
                    await this.loadPaymentHistory();
                    break;
            }
        } catch (error) {
            console.error(`Failed to load ${tabName} data:`, error);
        }
    }

    async loadServices() {
        this.showLoading('services-container');
        
        try {
            const response = await fetch('/api/customer/services', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const services = await response.json();
            this.renderServices(services);
            this.tabData.set('services', services);
        } catch (error) {
            this.showError('services-container', 'Failed to load services');
        }
    }

    renderServices(services) {
        const container = document.getElementById('services-container');
        container.innerHTML = services.map(service => `
            <div class="service-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-all cursor-pointer" onclick="orderService('${service.product_id}')">
                <div class="aspect-video bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
                    <i data-lucide="printer" class="h-12 w-12 text-primary"></i>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900">${service.product_name}</h3>
                            <p class="text-sm text-gray-600">${service.enterprise_name}</p>
                        </div>
                        <span class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-full">
                            Popular
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">${service.description || 'Professional printing service with fast delivery'}</p>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-bold text-primary">₱${service.base_price}</span>
                        <div class="flex items-center gap-1 text-sm text-gray-500">
                            <i data-lucide="clock" class="h-3 w-3"></i>
                            <span>3-5 days</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="saveService(event, '${service.product_id}')" class="flex-1 px-3 py-2 border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm font-medium">
                            <i data-lucide="heart" class="h-4 w-4 inline mr-1"></i>
                            Save
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    async loadOrders() {
        this.showLoading('orders-container');
        
        try {
            const response = await fetch('/api/customer/orders', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const orders = await response.json();
            this.renderOrders(orders);
            this.tabData.set('orders', orders);
        } catch (error) {
            this.showError('orders-container', 'Failed to load orders');
        }
    }

    renderOrders(orders) {
        const container = document.getElementById('orders-container');
        
        if (orders.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center">
                    <i data-lucide="package" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
                    <p class="text-gray-600 mb-4">Start browsing our services to place your first order</p>
                    <button onclick="switchToServicesTab()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Browse Services
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = orders.map(order => `
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i data-lucide="printer" class="h-6 w-6 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Order #${order.purchase_order_id.substring(0, 8)}</h4>
                                <p class="text-sm text-gray-600">${order.enterprise_name} • ${order.created_at}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₱${order.total}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                ${this.getStatusClass(order.status_name)}">
                                ${order.status_name}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    getStatusClass(status) {
        const statusClasses = {
            'Pending': 'bg-warning/10 text-warning',
            'In Progress': 'bg-primary/10 text-primary',
            'Delivered': 'bg-success/10 text-success',
            'Ready for Pickup': 'bg-success/10 text-success'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    async loadPaymentHistory() {
        this.showLoading('payments-container');
        
        try {
            const response = await fetch('/api/customer/payments', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const payments = await response.json();
            this.renderPayments(payments);
            this.tabData.set('payments', payments);
        } catch (error) {
            this.showError('payments-container', 'Failed to load payment history');
        }
    }

    renderPayments(payments) {
        const container = document.getElementById('payments-container');
        
        if (payments.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center">
                    <i data-lucide="credit-card" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No payment history</h3>
                    <p class="text-gray-600">Your payment transactions will appear here</p>
                </div>
            `;
        } else {
            container.innerHTML = payments.map(payment => `
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                                <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">${payment.description}</h4>
                                <p class="text-sm text-gray-600">${payment.date} • ${payment.method}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₱${payment.amount}</p>
                            <button class="text-primary hover:text-primary/80 text-sm">
                                Download Invoice
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    setupSearchAndFilters() {
        const searchInput = document.getElementById('service-search');
        const categoryFilter = document.getElementById('category-filter');
        const priceFilter = document.getElementById('price-filter');
        const sortFilter = document.getElementById('sort-filter');

        let searchTimeout;
        searchInput?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.filterServices();
            }, 300);
        });

        [categoryFilter, priceFilter, sortFilter].forEach(filter => {
            filter?.addEventListener('change', () => {
                this.filterServices();
            });
        });
    }

    async filterServices() {
        const searchTerm = document.getElementById('service-search').value;
        const category = document.getElementById('category-filter').value;
        const priceRange = document.getElementById('price-filter').value;
        const sortBy = document.getElementById('sort-filter').value;

        this.showLoading('services-container');

        try {
            const params = new URLSearchParams({
                search: searchTerm,
                category: category,
                price_range: priceRange,
                sort: sortBy
            });

            const response = await fetch(`/api/customer/services?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const services = await response.json();
            this.renderServices(services);
        } catch (error) {
            this.showError('services-container', 'Failed to filter services');
        }
    }

    setupProfileForm() {
        const form = document.getElementById('profile-form');
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.textContent = 'Saving...';
            submitButton.disabled = true;

            try {
                const response = await fetch('/api/customer/profile', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const result = await response.json();
                
                if (result.success) {
                    this.showSuccess('Profile updated successfully!');
                } else {
                    this.showError('profile-form', result.message || 'Failed to update profile');
                }
            } catch (error) {
                this.showError('profile-form', 'Network error. Please try again.');
            } finally {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
    }

    showLoading(containerId) {
        document.getElementById(containerId).innerHTML = `
            <div class="space-y-4">
                ${Array(4).fill().map(() => `
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="skeleton h-4 w-3/4 mb-2 rounded"></div>
                        <div class="skeleton h-3 w-1/2 mb-3 rounded"></div>
                        <div class="skeleton h-6 w-1/4 rounded"></div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    showError(containerId, message) {
        document.getElementById(containerId).innerHTML = `
            <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                <i data-lucide="alert-circle" class="h-12 w-12 text-red-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                <p class="text-gray-600">${message}</p>
            </div>
        `;
    }

    showSuccess(message) {
        // Create success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-20 right-4 bg-success text-white px-6 py-3 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    loadInitialTab() {
        const savedTab = localStorage.getItem('activeDashboardTab');
        if (savedTab && ['services', 'orders', 'account', 'payments', 'support'].includes(savedTab)) {
            this.switchTab(savedTab);
        }
    }

    saveTabPreference(tabName) {
        localStorage.setItem('activeDashboardTab', tabName);
    }
}

// Global functions for service actions
function saveService(event, productId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    fetch('/saved-services/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            service_id: productId,
            quantity: 1,
            customizations: []
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Service saved successfully!');
            } else {
                showNotification('Failed to save service', 'error');
            }
        })
        .catch(() => {
            showNotification('Failed to save service', 'error');
        });
}

function orderService(productId) {
    window.location.href = `/customer/services/${productId}`;
}

function switchToServicesTab() {
    document.querySelector('[data-tab="services"]').click();
}

function updateSavedServicesBadge(count) {
    const badge = document.querySelector('.saved-services-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-success text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="h-5 w-5"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize tab manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.tabManager = new TabManager();
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Load saved services count
    fetch('/saved-services/count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSavedServicesBadge(data.count);
            }
        })
        .catch(error => {
            console.error('Failed to load saved services count:', error);
        });
});
</script>
@endpush
@endsection
