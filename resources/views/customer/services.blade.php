@extends('layouts.public')

@section('title', 'Services')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav class="mb-2" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer.marketplace') }}">Shops</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $enterprise->name ?? 'Shop' }}</li>
            </ol>
        </nav>
        <h2 class="mb-0"><i class="bi bi-box-seam me-2"></i>{{ $enterprise->name ?? 'Unknown Enterprise' }}</h2>
        <p class="text-muted mb-0">
            <span class="badge bg-info me-2">{{ $enterprise->category }}</span>
            Browse available services
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-danger" data-up-report data-entity-type="enterprise" data-enterprise-id="{{ $enterprise->enterprise_id }}">
            <i class="bi bi-flag me-2"></i>Report Shop
        </button>
        <a href="{{ route('customer.marketplace') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Shops
        </a>
    </div>
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
                    <strong>Email:</strong> {{ $enterprise->email ?? 'Not specified' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div class="row g-4">
    @forelse($services as $service)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm" role="button" onclick="window.location.href='{{ route('customer.service.details', $service->service_id) }}'">
            @if(!empty($service->image_path))
                <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">{{ $service->service_name }}</h5>
                    <span class="badge bg-success">Available</span>
                </div>

                <p class="card-text text-muted mb-3">
                    {{ $service->description ? \Illuminate\Support\Str::limit($service->description, 100) : 'No description available' }}
                </p>

                <div class="border-top pt-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Starting at</span>
                        <h4 class="text-primary mb-0">₱{{ number_format($service->base_price, 2) }}</h4>
                    </div>
                </div>

                @if($service->customizationOptions->count() > 0)
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="bi bi-gear me-1"></i>
                        {{ $service->customizationOptions->count() }} customization options available
                    </small>
                </div>
                @endif
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
                    <a href="{{ route('customer.marketplace') }}" class="btn btn-primary">
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
