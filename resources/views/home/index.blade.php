@extends('layouts.public')

@section('title', 'UniPrint - Smart Printing Services for Baguio')

@push('styles')
<meta name="description" content="UniPrint - AI-enhanced printing platform connecting customers with local printing businesses. Order custom prints with AI design tools, instant chatbot support, and real-time tracking.">
<meta name="keywords" content="printing services, AI design, online printing, business cards, flyers, banners, custom printing, print shop, Baguio">
<meta name="author" content="UniPrint">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="UniPrint - Smart Printing Services for Baguio">
<meta property="og:description" content="AI-enhanced printing platform with design tools, chatbot support, and real-time tracking">
<meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:title" content="UniPrint - Smart Printing Services for Baguio">
@endpush

@section('content')

<!-- Hero Section -->
<section class="hero relative overflow-hidden">
    <div class="hero-gradient-bg"></div>
    
    <div class="container hero-content">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-8">
                <div class="hero-badge">
                    <i data-lucide="store" class="h-4 w-4"></i>
                    AI-Enhanced Printing Platform
                </div>
                
                <h1 class="text-foreground">
                    UniPrint: Smart Printing Services for Baguio
                </h1>
                
                <p class="lead">
                    Order custom prints online with AI-powered design tools, instant chatbot support, and real-time job tracking. Modernizing Baguio's printing industry.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('enterprises.index') }}" class="btn btn-hero btn-xl">
                        Browse Printing Shops
                        <i data-lucide="arrow-right" class="ml-2 h-5 w-5"></i>
                    </a>
                    
                    <a href="{{ route('ai-design.index') }}" class="btn btn-outline btn-xl">
                        Try AI Design Tool
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value">{{ $enterprises->count() }}+</div>
                        <div class="hero-stat-label">Printing Shops</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value">10K+</div>
                        <div class="hero-stat-label">Print Jobs</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value">AI</div>
                        <div class="hero-stat-label">Powered</div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute inset-0 gradient-primary opacity-20 blur-3xl rounded-full"></div>
                <div class="relative rounded-2xl shadow-card-hover w-full h-96 bg-gradient-to-br from-primary/20 to-accent/20 flex items-center justify-center">
                    <div class="text-center">
                        <i data-lucide="printer" class="h-24 w-24 text-primary mx-auto mb-4"></i>
                        <h3 class="text-xl font-semibold text-foreground">Professional Printing</h3>
                        <p class="text-muted-foreground">Connected digitally</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features section">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">AI-Enhanced Printing Platform</h2>
            <p class="lead max-w-2xl mx-auto">
                UniPrint combines traditional printing services with cutting-edge AI technology to streamline operations and enhance customer experience
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="card card-hover border-border shadow-card transition-smooth">
                <div class="card-body pt-6">
                    <div class="feature-icon">
                        <i data-lucide="sparkles" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">AI-Powered Design Tools</h3>
                    <p class="text-muted-foreground">Generate custom designs with our built-in AI image generation tool. Create professional prints without design experience.</p>
                </div>
            </div>

            <div class="card card-hover border-border shadow-card transition-smooth">
                <div class="card-body pt-6">
                    <div class="feature-icon">
                        <i data-lucide="message-square" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Smart Chatbot Support</h3>
                    <p class="text-muted-foreground">Get instant answers to your questions with our AI chatbot. Real-time assistance for orders, pricing, and specifications.</p>
                </div>
            </div>

            <div class="card card-hover border-border shadow-card transition-smooth">
                <div class="card-body pt-6">
                    <div class="feature-icon">
                        <i data-lucide="trending-up" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Real-Time Job Tracking</h3>
                    <p class="text-muted-foreground">Track your print jobs from order placement to completion with automated status updates and invoicing.</p>
                </div>
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
            <p class="lead">Your printing dashboard with AI-powered tools and real-time tracking</p>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('ai-design.index') }}" class="card card-hover shadow-card transition-smooth">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i data-lucide="sparkles" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">AI Design</h3>
                    <p class="text-sm text-muted-foreground">Create designs with AI</p>
                </div>
            </a>
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
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">Featured Printing Businesses</h2>
            <p class="lead">Connect with professional printing services in your area</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($enterprises as $enterprise)
            <div class="card card-hover shadow-card transition-smooth">
                <div class="card-body">
                    <div class="feature-icon mb-4">
                        <i data-lucide="building" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <span class="badge badge-primary mb-3">{{ $enterprise->category }}</span>
                    <h3 class="text-xl font-semibold mb-3">{{ $enterprise->enterprise_name }}</h3>
                    
                    @if($enterprise->address_text)
                    <p class="text-muted-foreground mb-2 flex items-center">
                        <i data-lucide="map-pin" class="h-4 w-4 mr-2"></i>
                        {{ Str::limit($enterprise->address_text, 40) }}
                    </p>
                    @endif
                    
                    <p class="text-muted-foreground mb-4 flex items-center">
                        <i data-lucide="package" class="h-4 w-4 mr-2"></i>
                        {{ $enterprise->services_count }} Services Available
                    </p>
                    
                    <a href="{{ route('enterprises.show', $enterprise->enterprise_id) }}" class="btn btn-outline btn-md w-full">
                        View Services
                        <i data-lucide="arrow-right" class="ml-2 h-4 w-4"></i>
                    </a>
                </div>
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
        <div class="text-center mt-12">
            <a href="{{ route('enterprises.index') }}" class="btn btn-primary btn-lg">
                View All Businesses
                <i data-lucide="arrow-right" class="ml-2 h-5 w-5"></i>
            </a>
        </div>
        @endif
    </div>
</section>

<!-- How It Works Section -->
<section class="section bg-secondary/30">
    <div class="container">
        <div class="text-center mb-12">
            <h2 class="mb-4">How It Works</h2>
            <p class="lead">Get your printing done in 4 simple steps</p>
        </div>
        
        <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="feature-icon mx-auto mb-4">
                    <i data-lucide="search" class="h-6 w-6 text-primary-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">1. Browse</h3>
                <p class="text-muted-foreground">Find printing businesses and products</p>
            </div>
            <div class="text-center">
                <div class="feature-icon mx-auto mb-4">
                    <i data-lucide="sliders" class="h-6 w-6 text-primary-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">2. Customize</h3>
                <p class="text-muted-foreground">Select options and specifications</p>
            </div>
            <div class="text-center">
                <div class="feature-icon mx-auto mb-4">
                    <i data-lucide="shopping-cart" class="h-6 w-6 text-primary-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">3. Order</h3>
                <p class="text-muted-foreground">Place your order and make payment</p>
            </div>
            <div class="text-center">
                <div class="feature-icon mx-auto mb-4">
                    <i data-lucide="truck" class="h-6 w-6 text-primary-foreground"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">4. Receive</h3>
                <p class="text-muted-foreground">Get your prints delivered or pick up</p>
            </div>
        </div>
    </div>
</section>

@include('components.chatbot')

@endsection
