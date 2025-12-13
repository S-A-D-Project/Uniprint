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
        <button class="px-4 py-2 bg-primary text-white rounded-full font-medium text-sm hover:bg-primary/90 transition-colors filter-btn active" data-filter="all">
            All
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors filter-btn" data-filter="print-shop">
            Print Shop
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors filter-btn" data-filter="t-shirt">
            T-Shirt Printing
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors filter-btn" data-filter="large-format">
            Large Format
        </button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium text-sm hover:bg-gray-200 transition-colors filter-btn" data-filter="business-cards">
            Business Cards
        </button>
    </div>
</div>

<!-- Printing Shops Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="shops-container">
    <!-- Kebs Enterprise -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-500 to-purple-700 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Kebs Enterprise</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">5.0</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>36 Lower Bonifacio St, Barangay ABCR, Baguio</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Sat: 8:00 AM–6:00 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 999 888 3955</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>

    <!-- Point and Print Printing Services -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-600 to-purple-800 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Point and Print Printing Services</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">5.0</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Session Rd, Baguio, Benguet</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Sat: 8:00 AM–8:00 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 907 159 8561</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>

    <!-- PRINTOREX Digital Printing Shop -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-700 to-purple-900 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">PRINTOREX Digital Printing Shop</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">5.0</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Digital Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>214, Mabini Shopping center, Baguio</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Fri: 9:00 AM–6:30 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 950 426 5889</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>

    <!-- Anndreleigh Photocopy Services -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-500 to-purple-700 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Anndreleigh Photocopy Services</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">4.8</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Photocopy & Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>7A purok 1 bal marcoville, PNR, Baguio</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Sat: 9:00 AM–8:00 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 997 108 9173</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>

    <!-- Printitos Printing Services -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-600 to-purple-800 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Printitos Printing Services</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">4.5</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Professional Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>99 Mabini St, Baguio, 2600 Benguet</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Fri: 9:30 AM–7:30 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 992 356 4390</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>

    <!-- Higher-UP Printing -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow shop-card" data-category="print-shop">
        <div class="h-48 bg-gradient-to-br from-purple-700 to-purple-900 relative">
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900">Higher-UP Printing</h3>
                <div class="flex items-center gap-1">
                    <i data-lucide="star" class="h-4 w-4 text-yellow-400 fill-current"></i>
                    <span class="text-sm font-medium text-gray-700">2.2</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">Basic Printing Services</p>
            <div class="space-y-2 text-sm text-gray-600 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4 flex-shrink-0"></i>
                    <span>119 Manuel Roxas, Baguio, Benguet</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 flex-shrink-0"></i>
                    <span>Mon-Sat: 9:00 AM–6:30 PM</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="phone" class="h-4 w-4 flex-shrink-0"></i>
                    <span>+63 74 422 5121</span>
                </div>
            </div>
            <button class="w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                View Services
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const shopCards = document.querySelectorAll('.shop-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-primary', 'text-white');
            
            // Filter shops
            shopCards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Search functionality
    const searchInput = document.getElementById('shop-search');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        shopCards.forEach(card => {
            const shopName = card.querySelector('h3').textContent.toLowerCase();
            const shopDescription = card.querySelector('p').textContent.toLowerCase();
            
            if (shopName.includes(searchTerm) || shopDescription.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
@endpush
@endsection
