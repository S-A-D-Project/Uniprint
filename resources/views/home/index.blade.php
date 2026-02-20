@extends('layouts.public')

@section('title', 'UniPrint - Smart Printing Services for Baguio')

@push('styles')
<meta name="description" content="UniPrint - Smart printing platform connecting customers with local printing businesses. Order custom prints with instant chatbot support, and real-time tracking.">
<meta name="keywords" content="printing services, online printing, business cards, flyers, banners, custom printing, print shop, Baguio">
<meta name="author" content="UniPrint">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="UniPrint - Smart Printing Services for Baguio">
<meta property="og:description" content="Smart printing platform with chatbot support, and real-time tracking">
<meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:title" content="UniPrint - Smart Printing Services for Baguio">
@endpush

@section('content')

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-primary/5 to-background">
    <div class="container mx-auto px-4 py-12 lg:py-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-medium">
                    <i data-lucide="store" class="h-4 w-4"></i>
                    Smart Printing Platform
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Smart printing platform connecting customers with local printing shops in Baguio City.
                    </p>
                    <h1 class="text-4xl md:text-5xl font-bold tracking-tight">Order custom prints online</h1>
                    <p class="mt-4 text-muted-foreground text-base md:text-lg leading-relaxed">
                        Find the perfect printing shop for your needs, instant chatbot support, and real-time job tracking. Modernizing Baguio's printing industry.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('enterprises.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-primary text-primary-foreground font-semibold hover:bg-primary/90 transition-colors">
                        Browse Printing Shops
                        <i data-lucide="arrow-right" class="h-5 w-5"></i>
                    </a>
                </div>

                <div class="flex gap-10 pt-2">
                    <div>
                        <div class="text-2xl font-bold text-primary">{{ $stats['total_enterprises'] ?? $enterprises->count() }}+</div>
                        <div class="text-xs text-muted-foreground">Printing Shops</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-primary">10K+</div>
                        <div class="text-xs text-muted-foreground">Print Jobs</div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-10 bg-primary/10 blur-3xl rounded-full"></div>
                <div class="relative bg-card border border-border rounded-2xl shadow-card-hover overflow-hidden">
                    <div class="h-64 sm:h-80 bg-gradient-to-br from-primary/10 to-accent/10 flex items-center justify-center">
                        <div class="text-center px-6">
                            <div class="mx-auto w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
                                <i data-lucide="printer" class="h-7 w-7 text-primary"></i>
                            </div>
                            <div class="font-semibold">Professional Printing</div>
                            <div class="text-sm text-muted-foreground">Connected digitally</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-14 bg-background">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-wider text-muted-foreground mb-2">Printing Platform</div>
            <h2 class="text-3xl md:text-4xl font-bold">Smart Printing Platform</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                UniPrint connects you with professional printing services to streamline operations and enhance customer experience
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="sparkles" class="h-5 w-5"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Design Tools</h3>
                <p class="text-sm text-muted-foreground">Upload your own designs or work with our design partners. Popular formats include PDF, PNG, and AI files.</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="message-square" class="h-5 w-5"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Smart Chatbot Support</h3>
                <p class="text-sm text-muted-foreground">Get instant answers to your questions with our smart chatbot. Real-time assistance for orders, pricing, and specifications.</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="trending-up" class="h-5 w-5"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Real-Time Job Tracking</h3>
                <p class="text-sm text-muted-foreground">Track your print jobs from order placement to completion with automated status updates and invoicing.</p>
            </div>
        </div>
    </div>
</section>

@if(session()->has('user_id'))
<!-- Customer Dashboard Section -->
<section class="section bg-secondary/30">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="text-center mb-12">
            <h2 class="mb-4">Welcome back, {{ explode(' ', session('user_name', 'Customer'))[0] }}! 👋</h2>
            <p class="lead">Your printing dashboard with smart tools and real-time tracking</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="card shadow-card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Orders</p>
                            <p class="text-2xl font-bold text-primary">{{ $stats['total_orders'] ?? 0 }}</p>
                        </div>
                        <i data-lucide="shopping-bag" class="h-8 w-8 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card shadow-card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">AI Designs</p>
                            <p class="text-2xl font-bold text-primary">{{ $stats['total_assets'] ?? 0 }}</p>
                        </div>
                        <i data-lucide="sparkles" class="h-8 w-8 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card shadow-card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending Orders</p>
                            <p class="text-2xl font-bold text-primary">{{ $stats['pending_orders'] ?? 0 }}</p>
                        </div>
                        <i data-lucide="clock" class="h-8 w-8 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="card shadow-card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Completed</p>
                            <p class="text-2xl font-bold text-primary">{{ $stats['completed_orders'] ?? 0 }}</p>
                        </div>
                        <i data-lucide="check-circle" class="h-8 w-8 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('customer.orders') }}" class="card card-hover shadow-card transition-smooth">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i data-lucide="package" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">My Orders</h3>
                    <p class="text-sm text-muted-foreground">Track your orders</p>
                </div>
            </a>
            <a href="{{ route('saved-services.index') }}" class="card card-hover shadow-card transition-smooth">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i data-lucide="heart" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Saved Services</h3>
                    <p class="text-sm text-muted-foreground">Manage your saved services</p>
                </div>
            </a>
            <a href="{{ route('profile.index') }}" class="card card-hover shadow-card transition-smooth">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i data-lucide="user" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Profile</h3>
                    <p class="text-sm text-muted-foreground">Manage your account</p>
                </div>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Featured Enterprises Section -->
<section class="section" id="enterprises">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <div class="text-sm text-muted-foreground mb-2">Connect with professional printing services in your area</div>
            <h2 class="text-3xl md:text-4xl font-bold">Featured Printing Businesses</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($enterprises as $enterprise)
                <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth p-6">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Printing Services</div>
                            <div class="text-lg font-semibold">{{ $enterprise->enterprise_name }}</div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground rounded-md">{{ $enterprise->category }}</span>
                    </div>

                    @if($enterprise->address_text)
                        <div class="flex items-start gap-2 text-sm text-muted-foreground mb-2">
                            <i data-lucide="map-pin" class="h-4 w-4 mt-0.5"></i>
                            <span>{{ Str::limit($enterprise->address_text, 55) }}</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-5">
                        <i data-lucide="package" class="h-4 w-4"></i>
                        <span>{{ $enterprise->services_count }} Services Available</span>
                    </div>

                    <a href="{{ route('enterprises.show', $enterprise->enterprise_id) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:bg-primary/90 transition-colors">
                        View Services
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <i data-lucide="store" class="h-16 w-16 text-muted-foreground mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">No printing businesses available yet</h3>
                    <p class="text-muted-foreground">Check back soon for our growing list of partners</p>
                </div>
            @endforelse
        </div>

        @if($enterprises->count() > 0)
            <div class="text-center mt-10">
                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors">
                    View All Businesses
                    <i data-lucide="arrow-right" class="h-5 w-5"></i>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- How It Works Section -->
<section class="py-14 bg-secondary/30">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <div class="text-sm text-muted-foreground mb-2">Get your printing done in 4 simple steps</div>
            <h2 class="text-3xl md:text-4xl font-bold">How It Works</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="search" class="h-5 w-5"></i>
                </div>
                <h3 class="text-base font-semibold mb-1">1. Browse</h3>
                <p class="text-sm text-muted-foreground">Find printing businesses and services</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="sliders" class="h-5 w-5"></i>
                </div>
                <h3 class="text-base font-semibold mb-1">2. Customize</h3>
                <p class="text-sm text-muted-foreground">Select options and specifications</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                </div>
                <h3 class="text-base font-semibold mb-1">3. Order</h3>
                <p class="text-sm text-muted-foreground">Place your order and make payment</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center mb-4">
                    <i data-lucide="truck" class="h-5 w-5"></i>
                </div>
                <h3 class="text-base font-semibold mb-1">4. Receive</h3>
                <p class="text-sm text-muted-foreground">Get your prints delivered or pick up</p>
            </div>
        </div>
    </div>
</section>

@endsection
