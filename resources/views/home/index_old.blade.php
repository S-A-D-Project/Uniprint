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
            <a href="{{ route('cart.index') }}" class="card card-hover shadow-card transition-smooth">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto mb-4">
                        <i data-lucide="shopping-cart" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Shopping Cart</h3>
                    <p class="text-sm text-muted-foreground">Review your cart</p>
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
                        {{ $enterprise->products_count }} Products Available
                    </p>
                    
                    <a href="{{ route('enterprises.show', $enterprise->enterprise_id) }}" class="btn btn-outline btn-md w-full">
                        View Products
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
                        </div>
                        <h3 class="text-lg font-semibold mb-2">AI Design Tool</h3>
                        <p class="text-muted-foreground text-sm">Create stunning designs with AI-powered tools</p>
                    </div>
                </a>

                <!-- My Orders -->
                <a href="{{ route('customer.orders') }}" class="group block">
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-border hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-lg bg-green-100">
                                <i class="bi bi-bag-check text-green-600 text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">My Orders</h3>
                        <p class="text-muted-foreground text-sm">Track and manage your print orders</p>
                    </div>
                </a>

                <!-- Shopping Cart -->
                <a href="{{ route('cart.index') }}" class="group block">
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-border hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-lg bg-orange-100">
                                <i class="bi bi-cart text-orange-600 text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Shopping Cart</h3>
                        <p class="text-muted-foreground text-sm">Review items and checkout</p>
                    </div>
                </a>

                <!-- My Profile -->
                <a href="{{ route('profile.index') }}" class="group block">
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-border hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-lg bg-blue-100">
                                <i class="bi bi-person-circle text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">My Profile</h3>
                        <p class="text-muted-foreground text-sm">Manage account and settings</p>
                    </div>
                </a>
            </div>

            <!-- Recent Orders Section -->
            @if(isset($recent_orders) && $recent_orders->count() > 0)
            <div class="mt-12">
                <div class="bg-white rounded-xl shadow-lg border border-border overflow-hidden">
                    <div class="p-6 border-b border-border">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold">Recent Orders</h3>
                            <a href="{{ route('customer.orders') }}" class="text-primary hover:underline">View All</a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="text-left p-4 font-medium">Order</th>
                                    <th class="text-left p-4 font-medium">Shop</th>
                                    <th class="text-left p-4 font-medium">Amount</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-left p-4 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_orders->take(5) as $order)
                                <tr class="border-t border-border">
                                    <td class="p-4 font-medium">#{{ $order->order_no ?? substr($order->purchase_order_id, 0, 8) }}</td>
                                    <td class="p-4">{{ $order->enterprise_name ?? 'Unknown' }}</td>
                                    <td class="p-4 font-medium">₱{{ number_format($order->total ?? $order->total_order_amount ?? 0, 2) }}</td>
                                    <td class="p-4">
                                        @php
                                            $status = $order->status_name ?? $order->current_status ?? 'Pending';
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if($status == 'Pending') bg-yellow-100 text-yellow-800
                                            @elseif($status == 'In Progress') bg-blue-100 text-blue-800
                                            @elseif($status == 'Shipped') bg-purple-100 text-purple-800
                                            @elseif($status == 'Delivered') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-muted-foreground">{{ isset($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
    @endif

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Why Choose UniPrint?</h2>
                <p class="lead text-muted">Everything you need for professional printing services</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-shop"></i>
                        </div>
                        <h3>Trusted Partners</h3>
                        <p class="text-muted">Work with verified, professional printing businesses in your area</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h3>Custom Options</h3>
                        <p class="text-muted">Extensive customization options for all your printing needs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h3>Fast Turnaround</h3>
                        <p class="text-muted">Quick processing and delivery for urgent printing projects</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enterprises Section -->
    <section class="enterprise-section" id="enterprises">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Browse Printing Businesses</h2>
                <p class="lead text-muted">Find the perfect printing partner for your project</p>
            </div>

            <div class="row g-4">
                @forelse($enterprises as $enterprise)
                <div class="col-md-6 col-lg-4">
                    <div class="enterprise-card">
                        <div class="card-body p-4">
                            <div class="enterprise-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <span class="enterprise-category">{{ $enterprise->category }}</span>
                            <h4 class="mt-2 mb-3">{{ $enterprise->enterprise_name }}</h4>
                            
                            @if($enterprise->address_text)
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ Str::limit($enterprise->address_text, 40) }}
                            </p>
                            @endif
                            
                            <p class="product-count mb-3">
                                <i class="bi bi-box-seam me-1"></i>
                                {{ $enterprise->products_count }} Products Available
                            </p>
                            
                            <a href="{{ route('enterprises.show', $enterprise->enterprise_id) }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-right me-2"></i>View Products
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-shop" style="font-size: 4rem; color: #cbd5e1;"></i>
                        <h4 class="mt-3">No printing businesses available yet</h4>
                        <p class="text-muted">Check back soon for our growing list of partners</p>
                    </div>
                </div>
                @endforelse
            </div>

            @if($enterprises->count() > 0)
            <div class="text-center mt-5">
                <a href="{{ route('enterprises.index') }}" class="btn btn-primary btn-lg px-5">
                    View All Businesses <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            @endif
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="features-section" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">How It Works</h2>
                <p class="lead text-muted">Get your printing done in 4 simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-search"></i>
                        </div>
                        <h5 class="mt-3">1. Browse</h5>
                        <p class="text-muted">Find printing businesses and products</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <h5 class="mt-3">2. Customize</h5>
                        <p class="text-muted">Select options and specifications</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <h5 class="mt-3">3. Order</h5>
                        <p class="text-muted">Place your order securely</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h5 class="mt-3">4. Receive</h5>
                        <p class="text-muted">Get your products delivered</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2>Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers using UniPrint</p>
            <a href="{{ route('register') }}" class="btn btn-hero-primary btn-lg px-5">
                Create Free Account <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-printer-fill me-2"></i>UniPrint
                    </h5>
                    <p class="text-muted">Your premier platform for connecting with professional printing services.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Platform</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features">Features</a></li>
                        <li class="mb-2"><a href="#enterprises">Browse Shops</a></li>
                        <li class="mb-2"><a href="#how-it-works">How It Works</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Account</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('login') }}">Login</a></li>
                        <li class="mb-2"><a href="{{ route('register') }}">Sign Up</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="mb-3">Connect With Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; {{ date('Y') }} UniPrint. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Chatbot Component -->
    @include('components.chatbot')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scrolling -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
