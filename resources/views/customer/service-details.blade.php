@extends('layouts.app')

@section('title', 'Service Details')

@section('dashboard-route', route('customer.dashboard'))

@section('sidebar')
    <a href="{{ route('customer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('customer.enterprises') }}" class="nav-link active">
        <i class="bi bi-shop"></i>Browse Shops
    </a>
    <a href="{{ route('customer.orders') }}" class="nav-link">
        <i class="bi bi-bag"></i>My Orders
    </a>
    <a href="{{ route('customer.design-assets') }}" class="nav-link">
        <i class="bi bi-images"></i>My Designs
    </a>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0">{{ $service->service_name }}</h2>
        <p class="text-muted mb-0">
            <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="text-decoration-none">
                {{ $service->enterprise->name ?? $service->enterprise_name ?? 'Unknown Enterprise' }}
            </a>
        </p>
    </div>
    <a href="{{ route('customer.enterprise.services', $service->enterprise_id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @if(!empty($service->image_path))
        <div class="card mb-4">
            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="card-img-top" style="height: 320px; object-fit: cover;">
        </div>
        @endif
        <!-- Service Information -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="mb-2">{{ $service->service_name }}</h3>
                        <span class="badge bg-success">Available</span>
                    </div>
                    <h2 class="text-primary mb-0">₱{{ number_format($service->base_price, 2) }}</h2>
                </div>

                <p class="text-muted">{{ $service->description ?? 'No description available' }}</p>
            </div>
        </div>

        <!-- Order Form -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Place Order</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('checkout.from-service') }}" method="POST" id="orderForm">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service->service_id }}">

                    <div class="mb-4">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="1" min="1" required>
                    </div>

                    @if(isset($customizationGroups) && $customizationGroups->count() > 0)
                        @foreach($customizationGroups as $type => $options)
                            <div class="mb-4">
                                <label class="form-label">{{ $type }}</label>
                                @foreach($options as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="customizations[]"
                                               value="{{ $option->option_id }}" id="option_{{ $option->option_id }}">
                                        <label class="form-check-label" for="option_{{ $option->option_id }}">
                                            {{ $option->option_name }}
                                            @if($option->price_modifier != 0)
                                                <span class="text-muted">({{ $option->price_modifier > 0 ? '+' : '' }}₱{{ number_format($option->price_modifier, 2) }})</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif

                    <div class="mb-4">
                        <label for="notes" class="form-label">Special Instructions (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Any special requirements or notes for this order..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-cart-check me-2"></i>Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Shop Information -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Shop Details</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2">{{ $service->enterprise->name ?? $service->enterprise_name ?? 'Unknown Enterprise' }}</h6>
                @if(!empty($service->enterprise->category ?? null))
                <span class="badge bg-info mb-3">{{ $service->enterprise->category }}</span>
                @endif

                @if(!empty($service->enterprise->address ?? null))
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i>{{ $service->enterprise->address }}
                </p>
                @endif

                @if(!empty($service->enterprise->email ?? null))
                <p class="text-muted small mb-0">
                    <i class="bi bi-envelope me-1"></i>{{ $service->enterprise->email }}
                </p>
                @endif
            </div>
        </div>

        <!-- Pricing Info -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-tag me-2"></i>Pricing</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Base Price:</span>
                    <strong>₱{{ number_format($service->base_price, 2) }}</strong>
                </div>

                @if(isset($customizationGroups) && $customizationGroups->count() > 0)
                <hr>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Additional charges may apply based on your customizations
                </small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
