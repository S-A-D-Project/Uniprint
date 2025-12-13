@extends('layouts.public')

@section('title', $enterprise->name)

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <!-- Back Button -->
            <a href="{{ route('enterprises.index') }}" class="inline-flex items-center gap-2 text-sm mb-6 hover:underline">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to Shops
            </a>

            <!-- Enterprise Header -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6 mb-8">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="mb-2">{{ $enterprise->name }}</h1>
                        <span class="inline-block px-3 py-1 text-sm font-medium bg-secondary text-secondary-foreground rounded-md mb-3">
                            Printing Services
                        </span>
                        <div class="flex flex-wrap gap-4 items-center text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <i data-lucide="map-pin" class="h-4 w-4"></i>
                                <span>{{ $enterprise->address }}</span>
                            </div>
                            @if(!empty($enterprise->contact_number))
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="h-4 w-4"></i>
                                <span>{{ $enterprise->contact_number }}</span>
                            </div>
                            @endif
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="h-4 w-4"></i>
                                <span>Same day delivery</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 bg-primary/10 px-3 py-2 rounded-lg">
                        <i data-lucide="star" class="h-5 w-5 fill-primary text-primary"></i>
                        <span class="text-lg font-semibold">4.8</span>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-2">Services</h2>
                <p class="text-sm text-muted-foreground">Browse available printing services from this shop.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden group">
                        <!-- Service Image / Placeholder -->
                        <div class="w-full h-48 gradient-accent"></div>

                        <div class="p-6">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="text-xl font-bold mb-1">{{ $service->service_name ?? $service->product_name ?? 'Printing Service' }}</h3>
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground rounded-md">
                                        Printing Service
                                    </span>
                                </div>
                                @if($service->is_active)
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-success/10 text-success rounded-md">Available</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-muted text-muted-foreground rounded-md">Unavailable</span>
                                @endif
                            </div>
                            
                            <p class="text-muted-foreground text-sm mb-4 line-clamp-2">
                                {{ $service->description ?: 'Professional printing service with custom options and quality materials.' }}
                            </p>

                            <div class="text-2xl font-bold text-primary mb-4">
                                ₱{{ number_format($service->base_price, 2) }}
                                <span class="text-sm font-normal text-muted-foreground">starting from</span>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('services.show', $service->service_id) }}" 
                                   class="flex-1 text-center px-4 py-2 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                    View Details
                                </a>
                                <button type="button" 
                                        class="flex-1 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth"
                                        onclick="window.location.href='{{ route('services.show', $service->service_id) }}'">
                                    <i data-lucide="shopping-cart" class="h-4 w-4 mr-2"></i>
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <i data-lucide="printer" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                        <p class="text-lg text-muted-foreground">No printing services available yet</p>
                        <p class="text-sm text-muted-foreground mt-2">Check back later for available services</p>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
@endsection

