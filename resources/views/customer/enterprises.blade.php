@extends('layouts.app')

@section('title', 'Browse Shops')

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
<div class="page-header">
    <h2 class="mb-0"><i class="bi bi-shop me-2"></i>Printing Shops</h2>
    <p class="text-muted mb-0">Discover and browse printing services</p>
</div>

<div class="row g-4">
    @forelse($enterprises as $enterprise)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                        <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">{{ $enterprise->name }}</h5>
                        <span class="badge bg-info">{{ $enterprise->category }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    @if($enterprise->address)
                    <p class="text-muted small mb-2">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ \Illuminate\Support\Str::limit($enterprise->address, 50) }}
                    </p>
                    @endif
                    @if($enterprise->email)
                    <p class="text-muted small mb-0">
                        <i class="bi bi-envelope me-1"></i>
                        {{ $enterprise->email }}
                    </p>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-box me-1"></i>{{ $enterprise->services_count }} Services
                    </span>
                    <span class="badge bg-success">Active</span>
                </div>

                <a href="{{ route('customer.enterprise.services', $enterprise->enterprise_id) }}" 
                   class="btn btn-primary w-100">
                    <i class="bi bi-arrow-right-circle me-2"></i>View Services
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-shop"></i>
                    <h5>No printing shops available</h5>
                    <p class="text-muted">Check back later for available printing services</p>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($enterprises->hasPages())
<div class="mt-4">
    {{ $enterprises->links() }}
</div>
@endif
@endsection
