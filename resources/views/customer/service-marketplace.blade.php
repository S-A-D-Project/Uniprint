@extends('layouts.public')

@section('title', 'Service Marketplace - UniPrint')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header with Search -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-4 py-3">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="h-5 w-5 text-white"></i>
                    </div>
                    <span class="font-bold text-xl text-gray-900">Marketplace</span>
                </a>

                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="marketplace-search"
                               placeholder="Search for services, providers, or keywords..." 
                               class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="camera" class="h-5 w-5"></i>
                            </button>
                        </div>
                        
                        <!-- Auto-complete Dropdown -->
                        <div id="search-suggestions" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
                            <div class="max-h-64 overflow-y-auto">
                                <!-- Suggestions will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Actions -->
                <div class="flex items-center gap-3">
                    <button class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i data-lucide="bell" class="h-5 w-5"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <a href="{{ route('saved-services.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i data-lucide="heart" class="h-5 w-5"></i>
                        <span class="saved-services-badge absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" style="display: none;">0</span>
                    </a>
                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                        Become a Provider
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Category Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-6 overflow-x-auto py-3">
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-orange-600 border-b-2 border-orange-600 whitespace-nowrap" 
                        data-category="all">
                    <i data-lucide="grid" class="h-4 w-4"></i>
                    All Services
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="business">
                    <i data-lucide="briefcase" class="h-4 w-4"></i>
                    Business Services
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="design">
                    <i data-lucide="palette" class="h-4 w-4"></i>
                    Design & Creative
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="marketing">
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    Marketing
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="printing">
                    <i data-lucide="printer" class="h-4 w-4"></i>
                    Printing & Production
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="digital">
                    <i data-lucide="monitor" class="h-4 w-4"></i>
                    Digital Services
                </button>
                <button class="category-btn flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-b-2 border-transparent whitespace-nowrap" 
                        data-category="consulting">
                    <i data-lucide="users" class="h-4 w-4"></i>
                    Consulting
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Location Filter -->
                    <div class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="h-4 w-4 text-gray-500"></i>
                        <select id="location-filter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">All Locations</option>
                            <option value="manila">Manila</option>
                            <option value="quezon">Quezon City</option>
                            <option value="makati">Makati</option>
                            <option value="pasig">Pasig</option>
                            <option value="taguig">Taguig</option>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="flex items-center gap-2">
                        <i data-lucide="dollar-sign" class="h-4 w-4 text-gray-500"></i>
                        <select id="price-range-filter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Any Price</option>
                            <option value="0-500">Under ₱500</option>
                            <option value="500-2000">₱500 - ₱2,000</option>
                            <option value="2000-5000">₱2,000 - ₱5,000</option>
                            <option value="5000+">Above ₱5,000</option>
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div class="flex items-center gap-2">
                        <i data-lucide="star" class="h-4 w-4 text-gray-500"></i>
                        <select id="rating-filter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Any Rating</option>
                            <option value="4+">4+ Stars</option>
                            <option value="3+">3+ Stars</option>
                            <option value="2+">2+ Stars</option>
                        </select>
                    </div>

                    <!-- Service Type Filter -->
                    <div class="flex items-center gap-2">
                        <i data-lucide="tag" class="h-4 w-4 text-gray-500"></i>
                        <select id="service-type-filter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">All Types</option>
                            <option value="online">Online Service</option>
                            <option value="onsite">On-site Service</option>
                            <option value="delivery">With Delivery</option>
                        </select>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                    <button id="grid-view" class="px-3 py-1 bg-white rounded text-sm font-medium text-gray-900 shadow-sm">
                        <i data-lucide="grid" class="h-4 w-4"></i>
                    </button>
                    <button id="list-view" class="px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-900">
                        <i data-lucide="list" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <!-- Results Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <span id="results-count">Loading...</span> Services Found
                </h1>
                <p class="text-gray-600 mt-1">Discover the best services for your needs</p>
            </div>
            <div class="flex items-center gap-3">
                <select id="sort-by" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="relevance">Most Relevant</option>
                    <option value="popular">Most Popular</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="rating">Highest Rated</option>
                    <option value="newest">Newest First</option>
                </select>
            </div>
        </div>

        <!-- Services Grid/List Container -->
        <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Loading State -->
            <div class="col-span-full">
                <div class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
                </div>
            </div>
        </div>

        <!-- Load More -->
        <div class="text-center mt-8">
            <button id="load-more-services" class="px-6 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700 hidden">
                Load More Services
            </button>
        </div>
    </main>

    <!-- Service Detail Modal -->
    <div id="service-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Service Details</h2>
                    <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
                <div id="service-modal-content">
                    <!-- Service details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Shopee-inspired styles */
.category-btn {
    transition: all 0.2s ease;
}

.category-btn:hover {
    color: #ea580c;
    border-color: #fed7aa;
    background-color: #fff7ed;
}

.service-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    border-color: #ea580c;
}

.service-card .service-image {
    aspect-ratio: 1;
    object-fit: cover;
}

.rating-stars {
    color: #fbbf24;
}

.search-suggestion {
    transition: background-color 0.2s ease;
}

.search-suggestion:hover {
    background-color: #f9fafb;
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* List view styles */
.service-list-item {
    transition: all 0.2s ease;
}

.service-list-item:hover {
    background-color: #f9fafb;
    border-color: #ea580c;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .category-btn span {
        display: none;
    }
    
    .category-btn i {
        margin-right: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
class ServiceMarketplace {
    constructor() {
        this.currentPage = 1;
        this.hasMorePages = true;
        this.currentFilters = {
            category: 'all',
            search: '',
            location: '',
            priceRange: '',
            rating: '',
            serviceType: '',
            sortBy: 'relevance'
        };
        this.viewMode = 'grid';
        this.searchTimeout = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadServices();
        this.initializeSearch();
    }

    setupEventListeners() {
        // Category buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.setActiveCategory(e.currentTarget.dataset.category);
            });
        });

        // Filters
        document.getElementById('location-filter')?.addEventListener('change', (e) => {
            this.currentFilters.location = e.target.value;
            this.resetAndLoad();
        });

        document.getElementById('price-range-filter')?.addEventListener('change', (e) => {
            this.currentFilters.priceRange = e.target.value;
            this.resetAndLoad();
        });

        document.getElementById('rating-filter')?.addEventListener('change', (e) => {
            this.currentFilters.rating = e.target.value;
            this.resetAndLoad();
        });

        document.getElementById('service-type-filter')?.addEventListener('change', (e) => {
            this.currentFilters.serviceType = e.target.value;
            this.resetAndLoad();
        });

        // Sort
        document.getElementById('sort-by')?.addEventListener('change', (e) => {
            this.currentFilters.sortBy = e.target.value;
            this.resetAndLoad();
        });

        // View toggle
        document.getElementById('grid-view')?.addEventListener('click', () => {
            this.setViewMode('grid');
        });

        document.getElementById('list-view')?.addEventListener('click', () => {
            this.setViewMode('list');
        });

        // Load more
        document.getElementById('load-more-services')?.addEventListener('click', () => {
            this.loadMoreServices();
        });
    }

    initializeSearch() {
        const searchInput = document.getElementById('marketplace-search');
        const suggestionsContainer = document.getElementById('search-suggestions');

        searchInput?.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const searchTerm = e.target.value;

            if (searchTerm.length >= 2) {
                this.searchTimeout = setTimeout(() => {
                    this.fetchSearchSuggestions(searchTerm);
                }, 300);
            } else {
                suggestionsContainer.classList.add('hidden');
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#marketplace-search') && !e.target.closest('#search-suggestions')) {
                suggestionsContainer.classList.add('hidden');
            }
        });
    }

    async fetchSearchSuggestions(searchTerm) {
        try {
            const response = await fetch(`/api/marketplace/search-suggestions?q=${encodeURIComponent(searchTerm)}`);
            const suggestions = await response.json();
            this.displaySearchSuggestions(suggestions);
        } catch (error) {
            console.error('Failed to fetch search suggestions:', error);
        }
    }

    displaySearchSuggestions(suggestions) {
        const container = document.getElementById('search-suggestions');
        const content = container.querySelector('div');

        if (suggestions.length === 0) {
            container.classList.add('hidden');
            return;
        }

        content.innerHTML = suggestions.map(suggestion => `
            <div class="search-suggestion px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center gap-3" onclick="selectSuggestion('${suggestion.term}')">
                <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                <div>
                    <div class="font-medium text-gray-900">${suggestion.term}</div>
                    <div class="text-sm text-gray-500">${suggestion.category} • ${suggestion.count} results</div>
                </div>
            </div>
        `).join('');

        container.classList.remove('hidden');
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    setActiveCategory(category) {
        this.currentFilters.category = category;
        
        // Update button states
        document.querySelectorAll('.category-btn').forEach(btn => {
            if (btn.dataset.category === category) {
                btn.classList.add('text-orange-600', 'border-orange-600');
                btn.classList.remove('text-gray-600', 'border-transparent');
            } else {
                btn.classList.remove('text-orange-600', 'border-orange-600');
                btn.classList.add('text-gray-600', 'border-transparent');
            }
        });

        this.resetAndLoad();
    }

    setViewMode(mode) {
        this.viewMode = mode;
        
        const gridBtn = document.getElementById('grid-view');
        const listBtn = document.getElementById('list-view');
        const container = document.getElementById('services-container');

        if (mode === 'grid') {
            gridBtn.classList.add('bg-white', 'shadow-sm', 'text-gray-900');
            gridBtn.classList.remove('text-gray-600');
            listBtn.classList.remove('bg-white', 'shadow-sm', 'text-gray-900');
            listBtn.classList.add('text-gray-600');
            container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
        } else {
            listBtn.classList.add('bg-white', 'shadow-sm', 'text-gray-900');
            listBtn.classList.remove('text-gray-600');
            gridBtn.classList.remove('bg-white', 'shadow-sm', 'text-gray-900');
            gridBtn.classList.add('text-gray-600');
            container.className = 'space-y-4';
        }

        this.renderServices(this.currentServices);
    }

    resetAndLoad() {
        this.currentPage = 1;
        this.hasMorePages = true;
        this.currentServices = [];
        this.loadServices();
    }

    async loadServices() {
        try {
            this.showLoading();
            
            const params = new URLSearchParams({
                page: this.currentPage,
                category: this.currentFilters.category,
                search: this.currentFilters.search,
                location: this.currentFilters.location,
                price_range: this.currentFilters.priceRange,
                rating: this.currentFilters.rating,
                service_type: this.currentFilters.serviceType,
                sort_by: this.currentFilters.sortBy
            });

            const response = await fetch(`/api/marketplace/services?${params}`);
            const data = await response.json();

            if (this.currentPage === 1) {
                this.currentServices = data.services;
                this.renderServices(data.services);
            } else {
                this.currentServices = [...this.currentServices, ...data.services];
                this.appendServices(data.services);
            }

            this.updateResultsCount(data.total);
            this.hasMorePages = data.has_more;
            this.toggleLoadMoreButton();

        } catch (error) {
            console.error('Failed to load services:', error);
            this.showError('Failed to load services. Please try again.');
        }
    }

    renderServices(services) {
        const container = document.getElementById('services-container');
        
        if (this.viewMode === 'grid') {
            container.innerHTML = services.map(service => this.createServiceCard(service)).join('');
        } else {
            container.innerHTML = services.map(service => this.createServiceListItem(service)).join('');
        }

        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    appendServices(services) {
        const container = document.getElementById('services-container');
        
        if (this.viewMode === 'grid') {
            services.forEach(service => {
                container.insertAdjacentHTML('beforeend', this.createServiceCard(service));
            });
        } else {
            services.forEach(service => {
                container.insertAdjacentHTML('beforeend', this.createServiceListItem(service));
            });
        }

        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    createServiceCard(service) {
        return `
            <div class="service-card bg-white rounded-lg overflow-hidden cursor-pointer" onclick="viewServiceDetail('${service.service_id}')">
                <div class="relative">
                    <img src="${service.image_url || '/placeholder-service.jpg'}" 
                         alt="${service.title}" 
                         class="service-image w-full h-48 object-cover">
                    ${service.is_featured ? '<span class="absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 text-xs rounded-full font-medium">Featured</span>' : ''}
                    <button onclick="toggleFavorite(event, '${service.service_id}')" class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-colors">
                        <i data-lucide="heart" class="h-4 w-4 text-gray-600"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">${service.title}</h3>
                            <p class="text-xs text-gray-600 mb-2">${service.provider_name}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="rating-stars flex items-center">
                            ${this.createStarRating(service.rating)}
                            <span class="text-xs text-gray-600 ml-1">(${service.review_count})</span>
                        </div>
                        ${service.service_type === 'online' ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Online</span>' : ''}
                        ${service.service_type === 'delivery' ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Delivery</span>' : ''}
                    </div>
                    <p class="text-xs text-gray-600 mb-3 line-clamp-2">${service.description}</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-lg font-bold text-orange-600">₱${service.price}</span>
                            ${service.original_price ? `<span class="text-xs text-gray-500 line-through ml-1">₱${service.original_price}</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-500">
                            <i data-lucide="map-pin" class="h-3 w-3"></i>
                            <span>${service.location}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    createServiceListItem(service) {
        return `
            <div class="service-list-item bg-white rounded-lg p-4 border border-gray-200 cursor-pointer" onclick="viewServiceDetail('${service.service_id}')">
                <div class="flex gap-4">
                    <div class="relative flex-shrink-0">
                        <img src="${service.image_url || '/placeholder-service.jpg'}" 
                             alt="${service.title}" 
                             class="w-24 h-24 rounded-lg object-cover">
                        ${service.is_featured ? '<span class="absolute top-1 left-1 bg-orange-500 text-white px-1 py-0.5 text-xs rounded font-medium">Featured</span>' : ''}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">${service.title}</h3>
                                <p class="text-sm text-gray-600">${service.provider_name}</p>
                            </div>
                            <button onclick="toggleFavorite(event, '${service.service_id}')" class="text-gray-400 hover:text-red-500 transition-colors">
                                <i data-lucide="heart" class="h-5 w-5"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rating-stars flex items-center">
                                ${this.createStarRating(service.rating)}
                                <span class="text-sm text-gray-600 ml-1">(${service.review_count})</span>
                            </div>
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i data-lucide="map-pin" class="h-4 w-4"></i>
                                <span>${service.location}</span>
                            </div>
                            ${service.service_type === 'online' ? '<span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">Online</span>' : ''}
                        </div>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">${service.description}</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-lg font-bold text-orange-600">₱${service.price}</span>
                                ${service.original_price ? `<span class="text-sm text-gray-500 line-through ml-2">₱${service.original_price}</span>` : ''}
                            </div>
                            <button class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    createStarRating(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let stars = '';
        for (let i = 0; i < fullStars; i++) {
            stars += '<i data-lucide="star" class="h-4 w-4 fill-current"></i>';
        }
        if (hasHalfStar) {
            stars += '<i data-lucide="star-half" class="h-4 w-4 fill-current"></i>';
        }
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i data-lucide="star" class="h-4 w-4"></i>';
        }
        
        return stars;
    }

    updateResultsCount(total) {
        document.getElementById('results-count').textContent = total.toLocaleString();
    }

    toggleLoadMoreButton() {
        const button = document.getElementById('load-more-services');
        if (this.hasMorePages) {
            button.classList.remove('hidden');
        } else {
            button.classList.add('hidden');
        }
    }

    showLoading() {
        const container = document.getElementById('services-container');
        container.innerHTML = `
            <div class="col-span-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    ${Array(8).fill().map(() => `
                        <div class="bg-white rounded-lg overflow-hidden">
                            <div class="skeleton h-48"></div>
                            <div class="p-4">
                                <div class="skeleton h-4 w-3/4 mb-2 rounded"></div>
                                <div class="skeleton h-3 w-1/2 mb-3 rounded"></div>
                                <div class="skeleton h-6 w-1/4 rounded"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    showError(message) {
        const container = document.getElementById('services-container');
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i data-lucide="alert-circle" class="h-12 w-12 text-red-500 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                <p class="text-gray-600">${message}</p>
            </div>
        `;
    }

    async loadMoreServices() {
        this.currentPage++;
        await this.loadServices();
    }
}

// Global functions
function selectSuggestion(term) {
    document.getElementById('marketplace-search').value = term;
    document.getElementById('search-suggestions').classList.add('hidden');
    window.marketplace.currentFilters.search = term;
    window.marketplace.resetAndLoad();
}

function toggleFavorite(event, serviceId) {
    event.stopPropagation();
    
    fetch('/api/marketplace/toggle-favorite', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ service_id: serviceId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const heartIcon = event.currentTarget.querySelector('i');
            if (data.is_favorited) {
                heartIcon.classList.remove('text-gray-600');
                heartIcon.classList.add('text-red-500', 'fill-current');
            } else {
                heartIcon.classList.remove('text-red-500', 'fill-current');
                heartIcon.classList.add('text-gray-600');
            }
            
            updateSavedServicesBadge(data.saved_services_count);
        }
    })
    .catch(error => {
        console.error('Failed to toggle favorite:', error);
    });
}

function viewServiceDetail(serviceId) {
    // Show modal with service details
    const modal = document.getElementById('service-modal');
    const content = document.getElementById('service-modal-content');
    
    content.innerHTML = `
        <div class="p-6">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    fetch(`/api/marketplace/service/${serviceId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = createServiceDetailModal(data);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="p-6 text-center">
                    <i data-lucide="alert-circle" class="h-12 w-12 text-red-500 mx-auto mb-4"></i>
                    <p class="text-gray-600">Failed to load service details</p>
                </div>
            `;
        });
}

function closeServiceModal() {
    document.getElementById('service-modal').classList.add('hidden');
}

function createServiceDetailModal(service) {
    return `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <img src="${service.image_url || '/placeholder-service.jpg'}" 
                     alt="${service.title}" 
                     class="w-full rounded-lg">
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">${service.title}</h2>
                <div class="flex items-center gap-4 mb-4">
                    <div class="rating-stars flex items-center">
                        ${window.marketplace.createStarRating(service.rating)}
                        <span class="text-sm text-gray-600 ml-1">(${service.review_count} reviews)</span>
                    </div>
                    <div class="flex items-center gap-1 text-sm text-gray-500">
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                        <span>${service.location}</span>
                    </div>
                </div>
                <div class="mb-6">
                    <span class="text-3xl font-bold text-orange-600">₱${service.price}</span>
                    ${service.original_price ? `<span class="text-lg text-gray-500 line-through ml-2">₱${service.original_price}</span>` : ''}
                </div>
                <div class="prose max-w-none mb-6">
                    <h3 class="text-lg font-semibold mb-2">Description</h3>
                    <p class="text-gray-600">${service.description}</p>
                </div>
                <div class="flex gap-3">
                    <button class="flex-1 px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium">
                        Book Service
                    </button>
                    <button class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Contact Provider
                    </button>
                </div>
            </div>
        </div>
    `;
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

// Initialize marketplace when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.marketplace = new ServiceMarketplace();
    
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
