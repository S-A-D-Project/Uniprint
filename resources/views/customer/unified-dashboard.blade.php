@extends('layouts.customer-layout')

@section('title', 'Customer Dashboard')
@section('page_title', 'Customer Dashboard')

@php
    $showNavigation = true;
@endphp

@section('navigation_tabs')
<button class="tab-button py-4 px-1 border-b-2 border-primary text-primary font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="services" role="tab" aria-selected="true">
    <i data-lucide="shopping-bag" class="h-4 w-4"></i>
    Services Catalog
</button>
<button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="orders" role="tab" aria-selected="false">
    <i data-lucide="package" class="h-4 w-4"></i>
    My Orders
</button>
<button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="ai-design" role="tab" aria-selected="false">
    <i data-lucide="sparkles" class="h-4 w-4"></i>
    AI Design
</button>
<button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm whitespace-nowrap flex items-center gap-2" 
        data-tab="account" role="tab" aria-selected="false">
    <i data-lucide="user" class="h-4 w-4"></i>
    Account Settings
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
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="animate-pulse">
                <div class="h-32 bg-gray-200 rounded-lg mb-4"></div>
                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- My Orders Tab -->
<div id="orders-tab" class="tab-content hidden" role="tabpanel">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="package" class="h-6 w-6 text-primary"></i>
                    My Orders
                </h2>
                <p class="text-gray-600 mt-1">Track and manage your orders</p>
            </div>
        </div>
    </div>

    <!-- Order Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-bold text-warning" id="pending-count">0</p>
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
                    <p class="text-2xl font-bold text-primary" id="progress-count">0</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="settings" class="h-6 w-6 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-success" id="completed-count">0</p>
                </div>
                <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div id="orders-container" class="space-y-4">
        <!-- Orders will be loaded here -->
    </div>
</div>

<!-- AI Design Tab -->
<div id="ai-design-tab" class="tab-content hidden" role="tabpanel">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="sparkles" class="h-6 w-6 text-primary"></i>
                    AI Design Tool
                </h2>
                <p class="text-gray-600 mt-1">Create custom designs for your print jobs using AI</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Input Section -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="edit-3" class="h-5 w-5 text-primary"></i>
                    Design Prompt
                </h3>
                <p class="text-sm text-gray-600 mt-1">Describe what you want to create and let AI generate it for you</p>
            </div>
            <div class="p-4">
                <form id="ai-design-form" class="space-y-4">
                    <div>
                        <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">Design Description *</label>
                        <textarea class="customer-input" id="prompt" name="prompt" rows="4" 
                                  placeholder="E.g., Modern business card with blue gradient, company logo space, and contact information layout" required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Be specific about colors, style, elements, and layout</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="style" class="block text-sm font-medium text-gray-700 mb-2">Style</label>
                            <select class="customer-input" id="style" name="style">
                                <option value="modern">Modern</option>
                                <option value="classic">Classic</option>
                                <option value="minimalist">Minimalist</option>
                                <option value="vintage">Vintage</option>
                            </select>
                        </div>
                        <div>
                            <label for="size" class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                            <select class="customer-input" id="size" name="size">
                                <option value="square">Square (1:1)</option>
                                <option value="landscape">Landscape (16:9)</option>
                                <option value="portrait">Portrait (9:16)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="customer-button-primary flex-1" id="generate-btn">
                            <i data-lucide="sparkles" class="h-4 w-4 mr-2"></i>
                            <span class="btn-text">Generate Design</span>
                        </button>
                        <button type="button" class="customer-button-secondary" id="reset-btn">
                            <i data-lucide="rotate-ccw" class="h-4 w-4 mr-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Output Section -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="image" class="h-5 w-5 text-primary"></i>
                    Generated Design
                </h3>
            </div>
            <div class="p-4">
                <!-- Loading State -->
                <div id="loading-state" class="text-center py-12" style="display: none;">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">AI is creating your design...</h3>
                    <p class="text-gray-600">This usually takes 10-30 seconds</p>
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="text-center py-12">
                    <i data-lucide="image" class="h-16 w-16 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No design generated yet</h3>
                    <p class="text-gray-600">Describe your design idea and click "Generate Design"</p>
                </div>

                <!-- Result State -->
                <div id="result-state" style="display: none;">
                    <div class="mb-4">
                        <img id="generated-image" class="w-full rounded-lg border border-gray-200" alt="Generated design">
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="text-sm text-gray-600 space-y-1">
                            <div><span class="font-medium text-gray-900">Prompt:</span> <span id="result-prompt"></span></div>
                            <div><span class="font-medium text-gray-900">Style:</span> <span id="result-style"></span></div>
                            <div><span class="font-medium text-gray-900">Size:</span> <span id="result-size"></span></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button class="customer-button-primary" id="save-btn">
                            <i data-lucide="download" class="h-4 w-4 mr-2"></i>Save Design
                        </button>
                        <button class="customer-button-secondary" id="regenerate-btn">
                            <i data-lucide="rotate-ccw" class="h-4 w-4 mr-2"></i>Regenerate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Settings Tab -->
<div id="account-tab" class="tab-content hidden" role="tabpanel">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2 mb-6">
            <i data-lucide="user" class="h-6 w-6 text-primary"></i>
            Account Settings
        </h2>
        <p class="text-gray-600">Manage your account preferences and settings.</p>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Mobile responsiveness for tabs */
@media (max-width: 768px) {
    .tab-button {
        font-size: 0.75rem;
        padding: 0.75rem 0.5rem;
    }
    
    .tab-button i {
        height: 1rem;
        width: 1rem;
    }
    
    .tab-button span {
        display: none;
    }
    
    /* Show only icons on mobile */
    .tab-button.mobile-icon-only {
        justify-content: center;
    }
}

/* Enhanced hover effects */
.customer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions */
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
// Unified Tab Management System
class UnifiedTabManager {
    constructor() {
        this.activeTab = 'services';
        this.tabData = new Map();
        this.init();
    }

    init() {
        this.setupTabListeners();
        this.loadInitialTab();
        this.setupResponsiveHandling();
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
        
        const targetTab = document.getElementById(`${tabName}-tab`);
        if (targetTab) {
            targetTab.classList.remove('hidden');
        }

        // Load tab data if not cached
        if (!this.tabData.has(tabName)) {
            this.loadTabData(tabName);
        }

        this.activeTab = tabName;
        
        // Re-initialize Lucide icons
        this.initializeLucideIcons();
    }

    async loadTabData(tabName) {
        try {
            switch (tabName) {
                case 'services':
                    await this.loadServices();
                    break;
                case 'orders':
                    await this.loadOrders();
                    break;
                case 'ai-design':
                    this.setupAIDesign();
                    break;
                case 'account':
                    await this.loadAccountSettings();
                    break;
            }
            this.tabData.set(tabName, true);
        } catch (error) {
            console.error(`Failed to load ${tabName} data:`, error);
            CustomerLayout.showNotification(`Failed to load ${tabName} data`, 'error');
        }
    }

    async loadServices() {
        // Load services data
        const container = document.getElementById('services-container');
        if (container) {
            container.innerHTML = '<div class="col-span-full text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div></div>';
            
            // Simulate API call
            setTimeout(() => {
                container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Services will be loaded here</div>';
            }, 1000);
        }
    }

    async loadOrders() {
        // Load orders data
        const container = document.getElementById('orders-container');
        if (container) {
            container.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div></div>';
            
            // Update statistics
            document.getElementById('pending-count').textContent = '3';
            document.getElementById('progress-count').textContent = '2';
            document.getElementById('completed-count').textContent = '15';
            
            // Simulate API call
            setTimeout(() => {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">Orders will be loaded here</div>';
            }, 1000);
        }
    }

    setupAIDesign() {
        // AI Design functionality is already set up in the form
        this.initializeAIDesignForm();
    }

    async loadAccountSettings() {
        // Load account settings
        console.log('Loading account settings...');
    }

    initializeAIDesignForm() {
        const form = document.getElementById('ai-design-form');
        if (form && !form.hasEventListener) {
            form.addEventListener('submit', this.handleAIDesignSubmit.bind(this));
            form.hasEventListener = true;
        }
    }

    async handleAIDesignSubmit(e) {
        e.preventDefault();
        
        const prompt = document.getElementById('prompt').value;
        const style = document.getElementById('style').value;
        const size = document.getElementById('size').value;
        
        if (!prompt.trim()) {
            CustomerLayout.showNotification('Please enter a design description', 'error');
            return;
        }
        
        // Show loading state
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('result-state').style.display = 'none';
        document.getElementById('loading-state').style.display = 'block';
        
        const generateBtn = document.getElementById('generate-btn');
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 mr-2 animate-spin"></i>Generating...';
        this.initializeLucideIcons();
        
        try {
            // Simulate AI generation
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Show result
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('result-state').style.display = 'block';
            
            CustomerLayout.showNotification('Design generated successfully!', 'success');
        } catch (error) {
            console.error('Error:', error);
            CustomerLayout.showNotification('Failed to generate design', 'error');
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('empty-state').style.display = 'block';
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i data-lucide="sparkles" class="h-4 w-4 mr-2"></i><span class="btn-text">Generate Design</span>';
            this.initializeLucideIcons();
        }
    }

    setupResponsiveHandling() {
        // Handle responsive behavior
        const handleResize = () => {
            if (window.innerWidth <= 768) {
                // Mobile: show only icons
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.add('mobile-icon-only');
                });
            } else {
                // Desktop: show icons and text
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('mobile-icon-only');
                });
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    }

    loadInitialTab() {
        this.switchTab(this.activeTab);
    }

    initializeLucideIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Initialize the tab manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new UnifiedTabManager();
});
</script>
@endpush
