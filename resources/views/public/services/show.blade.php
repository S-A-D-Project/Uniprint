@extends('layouts.public')

@section('title', $service->service_name . ' - Printing Service')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('enterprises.index') }}" class="hover:text-primary">Shops</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('enterprises.show', $service->enterprise_id) }}" class="hover:text-primary">{{ $service->enterprise->name ?? 'Unknown Shop' }}</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground">{{ $service->service_name }}</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Service Image and Info -->
                <div>
                    @if(!empty($service->image_path))
                        <div class="h-96 bg-secondary rounded-xl mb-4 overflow-hidden">
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="w-full h-full object-cover" />
                        </div>
                    @else
                        <div class="h-96 gradient-accent rounded-xl mb-4 flex items-center justify-center">
                            <i data-lucide="printer" class="h-32 w-32 text-white"></i>
                        </div>
                    @endif
                    <div class="space-y-4">
                        <div>
                            <a href="{{ route('enterprises.show', $service->enterprise_id) }}" class="inline-block px-3 py-1 text-sm font-medium bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                                {{ $service->enterprise->name ?? 'Unknown Shop' }}
                            </a>
                        </div>

                        <div>
                            @if(session('user_id'))
                                <a href="{{ route('chat.enterprise', $service->enterprise_id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth js-open-enterprise-chat" data-enterprise-id="{{ $service->enterprise_id }}">
                                    <i data-lucide="message-circle" class="h-4 w-4"></i>
                                    Message this shop
                                </a>

                                @if(auth()->check() && auth()->user()->getUserRoleType() === 'customer')
                                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-destructive text-destructive font-medium rounded-md hover:bg-destructive/10 transition-smooth" data-up-report data-entity-type="service" data-service-id="{{ $service->service_id }}">
                                        <i data-lucide="flag" class="h-4 w-4"></i>
                                        Report
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('login', ['tab' => 'signup']) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-input rounded-md hover:bg-secondary transition-smooth">
                                    <i data-lucide="log-in" class="h-4 w-4"></i>
                                    Sign in to message
                                </a>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $service->service_name }}</h1>
                            <p class="text-lg text-muted-foreground">{{ $service->description ?? 'Professional printing service' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Starting Price</p>
                            <div class="text-4xl font-bold text-primary">
                                ₱{{ number_format($service->base_price, 2) }}
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">Price may vary based on customizations</p>
                        </div>
                    </div>
                </div>

                <!-- Order CTA (public page) -->
                <div class="space-y-6">
                    <div class="bg-card border border-border rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-2">Order</h3>
                        <p class="text-sm text-muted-foreground mb-4">To place an order, please continue to the ordering page.</p>

                        @if(session('user_id'))
                            <a href="{{ route('customer.service.details', $service->service_id) }}" class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth">
                                <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                                Continue to Order
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="block w-full text-center px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth">
                                Login to Order Service
                            </a>
                        @endif
                    </div>

                    <div class="bg-muted/30 border border-border rounded-lg p-6 text-center">
                        <p class="text-muted-foreground">Customization options will be selected during ordering.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
