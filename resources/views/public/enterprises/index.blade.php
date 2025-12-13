@extends('layouts.public')

@section('title', 'Printing Shops')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="mb-4">Printing Shops in Baguio</h1>
                <p class="text-lg text-muted-foreground mb-6">
                    Find the perfect printing shop for your needs with AI-enhanced ordering
                </p>

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground"></i>
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Search for printing shops..." 
                            class="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                        />
                    </div>
                </div>

                <div class="flex gap-2 mt-4 flex-wrap">
                    <button onclick="filterCategory('All')" class="category-btn px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground transition-smooth" data-category="All">
                        All
                    </button>
                    @foreach($categories as $category)
                        <button onclick="filterCategory('{{ $category }}')" class="category-btn px-4 py-2 text-sm font-medium rounded-md border border-input hover:bg-accent hover:text-accent-foreground transition-smooth" data-category="{{ $category }}">
                            {{ $category }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div id="enterprisesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($enterprises as $enterprise)
                    <div class="enterprise-card bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden group" data-category="All" data-name="{{ strtolower($enterprise->name) }}">
                        <div class="h-48 gradient-hero"></div>
                        
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold mb-1">{{ $enterprise->name }}</h3>
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground rounded-md">
                                        Printing Services
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 bg-primary/10 px-2 py-1 rounded">
                                    <i data-lucide="star" class="h-4 w-4 fill-primary text-primary"></i>
                                    <span class="text-sm font-medium">4.8</span>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-muted-foreground mb-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="h-4 w-4"></i>
                                    <span>{{ $enterprise->address }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clock" class="h-4 w-4"></i>
                                    <span>Same day delivery</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="phone" class="h-4 w-4"></i>
                                    <span>{{ $enterprise->contact_number }}</span>
                                </div>
                            </div>

                            <a href="{{ route('enterprises.show', $enterprise->enterprise_id) }}" class="block w-full text-center px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                                View Services
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <i data-lucide="inbox" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                        <p class="text-lg text-muted-foreground">No printing shops found</p>
                    </div>
                @endforelse
            </div>

            <div id="noResults" class="hidden text-center py-12">
                <i data-lucide="search-x" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                <p class="text-lg text-muted-foreground">No printing shops found matching your criteria</p>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
<script>
    let selectedCategory = 'All';
    
    function filterCategory(category) {
        selectedCategory = category;
        
        // Update button styles
        document.querySelectorAll('.category-btn').forEach(btn => {
            if (btn.dataset.category === category) {
                btn.className = 'category-btn px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground transition-smooth';
            } else {
                btn.className = 'category-btn px-4 py-2 text-sm font-medium rounded-md border border-input hover:bg-accent hover:text-accent-foreground transition-smooth';
            }
        });
        
        filterEnterprises();
    }
    
    function filterEnterprises() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.enterprise-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardCategory = card.dataset.category;
            const cardName = card.dataset.name;
            
            const matchesCategory = selectedCategory === 'All' || cardCategory === selectedCategory;
            const matchesSearch = cardName.includes(searchQuery);
            
            if (matchesCategory && matchesSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        document.getElementById('noResults').classList.toggle('hidden', visibleCount > 0);
    }
    
    // Search input listener
    document.getElementById('searchInput').addEventListener('input', filterEnterprises);
</script>
@endpush
