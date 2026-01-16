@extends('layouts.guest')

@section('title', $enterprise->enterprise_name)

@section('content')
<div class="container py-5">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('home.browse') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Browse
        </a>
    </div>

    <!-- Enterprise Header -->
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="enterprise-icon-large">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
                <div class="col">
                    <span class="badge bg-primary mb-2">{{ $enterprise->category }}</span>
                    <h1 class="h2 mb-2">{{ $enterprise->enterprise_name }}</h1>
                    
                    <div class="row g-3 text-muted">
                        @if($enterprise->address_text)
                        <div class="col-auto">
                            <i class="bi bi-geo-alt me-1"></i>{{ $enterprise->address_text }}
                        </div>
                        @endif
                        
                        @if($enterprise->contact_email)
                        <div class="col-auto">
                            <i class="bi bi-envelope me-1"></i>{{ $enterprise->contact_email }}
                        </div>
                        @endif
                        
                        <div class="col-auto">
                            <i class="bi bi-box-seam me-1"></i>{{ $enterprise->services_count }} Services
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="mb-4">
        <h3 class="mb-3">Available Services</h3>
        
        @if($services->count() > 0)
        <div class="row g-4">
            @foreach($services as $service)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 product-card">
                    <div class="card-body">
                        <div class="product-icon mb-3">
                            <i class="bi bi-box"></i>
                        </div>
                        
                        <h5 class="card-title">{{ $service->service_name }}</h5>
                        
                        @if($service->description_text)
                        <p class="card-text text-muted small mb-3">
                            {{ Str::limit($service->description_text, 100) }}
                        </p>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small">Starting at</span>
                                <h4 class="text-primary mb-0">₱{{ number_format($service->base_price, 2) }}</h4>
                            </div>
                            
                            @auth
                                @if(auth()->user()->role_type === 'customer')
                                <a href="{{ route('customer.service.details', $service->service_id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus me-1"></i>Order
                                </a>
                                @else
                                <span class="badge bg-secondary">View Only</span>
                                @endif
                            @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login to Order
                            </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($enterprise->services_count > 6)
        <div class="text-center mt-4">
            <p class="text-muted">
                Showing {{ $services->count() }} of {{ $enterprise->services_count }} services
            </p>
            @auth
                @if(auth()->user()->role_type === 'customer')
                <a href="{{ route('customer.enterprise.services', $enterprise->enterprise_id) }}" class="btn btn-primary">
                    View All Services <i class="bi bi-arrow-right ms-2"></i>
                </a>
                @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    Login as Customer to View All
                </a>
                @endif
            @else
            <a href="{{ route('login') }}" class="btn btn-primary">
                Login to View All Services
            </a>
            @endauth
        </div>
        @endif
        
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e1;"></i>
            <h4 class="mt-3">No services available yet</h4>
            <p class="text-muted">Check back soon for new services</p>
        </div>
        @endif
    </div>

    <!-- CTA Section -->
    @guest
    <div class="card bg-primary text-white">
        <div class="card-body text-center p-5">
            <h3 class="mb-3">Ready to Order?</h3>
            <p class="lead mb-4">Create a free account to place orders with {{ $enterprise->enterprise_name }}</p>
            <a href="{{ route('login', ['tab' => 'signup']) }}" class="btn btn-light btn-lg">
                Sign Up Free <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
    @endguest
</div>

<style>
    .enterprise-icon-large {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .product-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    
    .product-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }
</style>
@endsection
