@extends('layouts.public')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 gradient-hero opacity-10"></div>
        
        <div class="container mx-auto px-4 relative py-20 md:py-32">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-medium">
                        <i data-lucide="store" class="h-4 w-4"></i>
                        AI-Enhanced Printing Platform
                    </div>
                    
                    <h1 class="text-foreground">
                        UniPrint: Smart Printing Services for Baguio
                    </h1>
                    
                    <p class="text-xl text-muted-foreground max-w-xl">
                        Order custom prints online with AI-powered design tools, instant chatbot support, and real-time job tracking. Modernizing Baguio's printing industry.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('enterprises.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth">
                            Browse Printing Shops
                            <i data-lucide="arrow-right" class="h-5 w-5"></i>
                        </a>
                        
                        <a href="#" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold border border-input rounded-lg hover:bg-accent hover:text-accent-foreground transition-smooth">
                            Try AI Design Tool
                        </a>
                    </div>

                    <div class="flex gap-8 pt-4">
                        <div>
                            <div class="text-3xl font-bold text-primary">{{ $stats['total_enterprises'] }}+</div>
                            <div class="text-sm text-muted-foreground">Printing Shops</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-primary">{{ $stats['total_orders'] > 0 ? number_format($stats['total_orders']) : '100' }}+</div>
                            <div class="text-sm text-muted-foreground">Print Jobs</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-primary">{{ $stats['total_products'] }}+</div>
                            <div class="text-sm text-muted-foreground">Products</div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 gradient-primary opacity-20 blur-3xl rounded-full"></div>
                    <div class="relative rounded-2xl shadow-card-hover w-full h-96 gradient-hero flex items-center justify-center text-white">
                        <i data-lucide="printer" class="h-32 w-32"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-secondary/30">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="mb-4">AI-Enhanced Printing Platform</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    UniPrint combines traditional printing services with cutting-edge AI technology to streamline operations and enhance customer experience
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth p-6">
                    <div class="mb-4 inline-flex p-3 rounded-lg gradient-primary">
                        <i data-lucide="sparkles" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">AI-Powered Design Tools</h3>
                    <p class="text-muted-foreground">Generate custom designs with our built-in AI image generation tool. Create professional prints without design experience.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth p-6">
                    <div class="mb-4 inline-flex p-3 rounded-lg gradient-primary">
                        <i data-lucide="message-square" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Smart Chatbot Support</h3>
                    <p class="text-muted-foreground">Get instant answers to your questions with our AI chatbot. Real-time assistance for orders, pricing, and specifications.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth p-6">
                    <div class="mb-4 inline-flex p-3 rounded-lg gradient-primary">
                        <i data-lucide="trending-up" class="h-6 w-6 text-primary-foreground"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Real-Time Job Tracking</h3>
                    <p class="text-muted-foreground">Track your print jobs from order placement to completion with automated status updates and invoicing.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="mb-4">How UniPrint Works</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Get your prints done in just a few simple steps
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-xl">
                        1
                    </div>
                    <h3 class="text-lg font-bold mb-2">Browse Shops</h3>
                    <p class="text-muted-foreground text-sm">
                        Explore local printing shops and their services
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-xl">
                        2
                    </div>
                    <h3 class="text-lg font-bold mb-2">Customize Order</h3>
                    <p class="text-muted-foreground text-sm">
                        Select products and customize with our AI tools
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-xl">
                        3
                    </div>
                    <h3 class="text-lg font-bold mb-2">Place Order</h3>
                    <p class="text-muted-foreground text-sm">
                        Submit your order and make secure payment
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full gradient-primary flex items-center justify-center text-white font-bold text-xl">
                        4
                    </div>
                    <h3 class="text-lg font-bold mb-2">Track & Collect</h3>
                    <p class="text-muted-foreground text-sm">
                        Monitor progress and collect your prints
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Services Section -->
    <section class="py-20 bg-secondary/30">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="mb-4">Popular Services</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Most requested printing services in Baguio
                </p>
            </div>

            <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-6">
                @forelse($popularServices as $index => $service)
                    <a href="{{ route('products.show', $service->product_id) }}" class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth p-6 text-center block">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-lg {{ $index % 2 == 0 ? 'gradient-primary' : 'gradient-accent' }} flex items-center justify-center">
                            <i data-lucide="package" class="h-6 w-6 text-white"></i>
                        </div>
                        <h4 class="font-semibold mb-1">{{ $service->product_name }}</h4>
                        <p class="text-xs text-muted-foreground">From ₱{{ number_format($service->base_price, 2) }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ $service->enterprise_name }}</p>
                    </a>
                @empty
                    <!-- Fallback if no products -->
                    <div class="col-span-full text-center text-muted-foreground">
                        No services available yet
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    View All Services
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="mb-4">What Our Customers Say</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Real feedback from satisfied customers
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach($testimonials as $testimonial)
                    <div class="bg-card border border-border rounded-xl shadow-card p-6">
                        <div class="flex items-center gap-1 mb-4">
                            @for($i = 0; $i < $testimonial->rating; $i++)
                                <i data-lucide="star" class="h-4 w-4 fill-primary text-primary"></i>
                            @endfor
                        </div>
                        <p class="text-muted-foreground mb-4">
                            "{{ $testimonial->comment }}"
                        </p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $loop->index % 2 == 0 ? 'gradient-primary' : 'gradient-accent' }} flex items-center justify-center text-white font-bold">
                                {{ $testimonial->initial }}
                            </div>
                            <div>
                                <p class="font-medium">{{ $testimonial->name }}</p>
                                <p class="text-sm text-muted-foreground">{{ $testimonial->position }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-hero text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-white mb-4">Ready to Start Printing?</h2>
            <p class="text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                Join thousands of satisfied customers who trust UniPrint for their printing needs
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold bg-white text-primary rounded-lg hover:bg-white/90 transition-smooth">
                    Get Started Free
                    <i data-lucide="user-plus" class="h-5 w-5"></i>
                </a>
                <a href="{{ route('enterprises.index') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold border-2 border-white text-white rounded-lg hover:bg-white hover:text-primary transition-smooth">
                    Browse Shops
                    <i data-lucide="store" class="h-5 w-5"></i>
                </a>
            </div>
        </div>
    </section>
@endsection
