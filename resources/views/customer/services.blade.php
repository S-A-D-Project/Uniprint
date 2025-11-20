@extends('layouts.app')

@section('title', 'Services')

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
        <h2 class="mb-0"><i class="bi bi-box-seam me-2"></i>{{ $enterprise->name ?? 'Unknown Enterprise' }}</h2>
        <p class="text-muted mb-0">
            <span class="badge bg-info me-2">{{ $enterprise->category }}</span>
            Browse available services
        </p>
    </div>
    <a href="{{ route('customer.enterprises') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Shops
    </a>
</div>

<!-- Enterprise Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                    <strong>Address:</strong> {{ $enterprise->address ?? 'Not specified' }}
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <i class="bi bi-envelope me-2 text-primary"></i>
                    <strong>Email:</strong> {{ $enterprise->contact_email ?? 'Not specified' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div class="row g-4">
    @forelse($services as $service)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">{{ $service->service_name }}</h5>
                    <span class="badge bg-success">Available</span>
                </div>

                <p class="card-text text-muted mb-3">
                    {{ $service->description_text ? Str::limit($service->description_text, 100) : 'No description available' }}
                </p>

                <div class="border-top pt-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Starting at</span>
                        <h4 class="text-primary mb-0">₱{{ number_format($service->base_price, 2) }}</h4>
                    </div>
                </div>

                @if($service->customizationGroups->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="bi bi-gear me-1"></i>
                        {{ $service->customizationGroups->count() }} customization options available
                    </small>
                </div>
                @endif

                <a href="{{ route('customer.service.details', $service->service_id) }}" 
                   class="btn btn-primary w-100">
                    <i class="bi bi-eye me-2"></i>View Details
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <h5>No services available</h5>
                    <p class="text-muted">This shop hasn't added any services yet</p>
                    <a href="{{ route('customer.enterprises') }}" class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i>Browse Other Shops
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($services->hasPages())
<div class="mt-4">
    {{ $services->links() }}
</div>
@endif
@endsection
