@extends('layouts.guest')

@section('title', 'Browse Printing Businesses')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold mb-2">Browse Printing Businesses</h1>
            <p class="lead text-muted">Find the perfect printing partner for your needs</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('home.browse') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="products" {{ request('sort') == 'products' ? 'selected' : '' }}>Most Products</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('home.browse') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="row g-4 mb-4">
        @forelse($enterprises as $enterprise)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 enterprise-card">
                <div class="card-body">
                    <div class="enterprise-icon mb-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <span class="badge bg-primary mb-2">{{ $enterprise->category }}</span>
                    <h5 class="card-title">{{ $enterprise->enterprise_name }}</h5>
                    
                    @if($enterprise->address_text)
                    <p class="card-text text-muted small mb-2">
                        <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($enterprise->address_text, 50) }}
                    </p>
                    @endif
                    
                    @if($enterprise->contact_email)
                    <p class="card-text text-muted small mb-3">
                        <i class="bi bi-envelope me-1"></i>{{ $enterprise->contact_email }}
                    </p>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">
                            <i class="bi bi-box-seam me-1"></i>{{ $enterprise->services_count }} services
                        </span>
                    </div>
                    
                    <a href="{{ route('home.enterprise', $enterprise->enterprise_id) }}" class="btn btn-outline-primary w-100">
                        View Products <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #cbd5e1;"></i>
                <h4 class="mt-3">No businesses found</h4>
                <p class="text-muted">Try adjusting your filters</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($enterprises->hasPages())
    <div class="d-flex justify-content-center">
        {{ $enterprises->links() }}
    </div>
    @endif
</div>

<style>
    .enterprise-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .enterprise-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    
    .enterprise-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
</style>
@endsection
