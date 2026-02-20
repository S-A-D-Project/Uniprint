@extends('layouts.public')

@section('title', 'Printing Shops')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground">Shops</span>
                </div>
            </div>

            <div class="mb-8">
                <h1 class="mb-4">Printing Shops in Baguio</h1>
                <p class="text-lg text-muted-foreground mb-6">
                    Find the perfect printing shop for your needs
                </p>

                <form method="GET" action="{{ route('enterprises.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-6">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Search</label>
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground"></i>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search for printing shops..."
                                class="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex items-end gap-2">
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Apply
                        </button>
                        <a href="{{ route('enterprises.index') }}" class="w-full text-center px-4 py-2 border border-input rounded-md hover:bg-secondary transition-smooth">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($enterprises as $enterprise)
                    <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden group">
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
                                    <span class="text-sm font-medium">{{ !empty($enterprise->review_count) ? number_format((float) ($enterprise->rating ?? 0), 1) : '—' }}</span>
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

            @if($enterprises->hasPages())
                <div class="mt-8 d-flex justify-content-center">
                    {{ $enterprises->appends(request()->query())->links() }}
                </div>
            @endif
        </main>
    </div>
@endsection
