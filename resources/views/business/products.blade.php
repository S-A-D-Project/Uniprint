@extends('layouts.app')

@section('title', 'Products')

@section('dashboard-route', route('business.dashboard'))

@section('sidebar')
    <a href="{{ route('business.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('business.orders') }}" class="nav-link">
        <i class="bi bi-cart"></i>Orders
    </a>
    <a href="{{ route('business.products') }}" class="nav-link active">
        <i class="bi bi-box-seam"></i>Products
    </a>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0"><i class="bi bi-box-seam me-2"></i>Products</h2>
        <p class="text-muted mb-0">Manage products for {{ $enterprise->enterprise_name }}</p>
    </div>
    <a href="{{ route('business.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
</div>

<div class="row g-4">
    @forelse($products as $product)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">{{ $product->product_name }}</h5>
                        <span class="badge bg-secondary">{{ $product->order_items_count }} sales</span>
                    </div>
                    @if($product->is_available)
                        <span class="badge bg-success">Available</span>
                    @else
                        <span class="badge bg-secondary">Unavailable</span>
                    @endif
                </div>

                <p class="card-text text-muted small mb-3">
                    {{ $product->description_text ? Str::limit($product->description_text, 100) : 'No description' }}
                </p>

                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="text-primary mb-0">₱{{ number_format($product->base_price, 2) }}</h4>
                    <small class="text-muted">Base Price</small>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <h5>No products yet</h5>
                    <p class="text-muted">Start by adding your first product</p>
                    <a href="{{ route('business.products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($products->hasPages())
<div class="mt-4">
    {{ $products->links() }}
</div>
@endif
@endsection
