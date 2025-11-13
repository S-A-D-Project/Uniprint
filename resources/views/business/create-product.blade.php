@extends('layouts.app')

@section('title', 'Create Product')

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
        <h2 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Product</h2>
        <p class="text-muted mb-0">Add a new product to {{ $enterprise->enterprise_name }}</p>
    </div>
    <a href="{{ route('business.products') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('business.products.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="product_name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                               id="product_name" name="product_name" value="{{ old('product_name') }}" 
                               required>
                        @error('product_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description_text" class="form-label">Description</label>
                        <textarea class="form-control @error('description_text') is-invalid @enderror" 
                                  id="description_text" name="description_text" rows="4">{{ old('description_text') }}</textarea>
                        @error('description_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Provide a detailed description of your product</small>
                    </div>

                    <div class="mb-4">
                        <label for="base_price" class="form-label">Base Price ($) *</label>
                        <input type="number" class="form-control @error('base_price') is-invalid @enderror" 
                               id="base_price" name="base_price" value="{{ old('base_price') }}" 
                               step="0.01" min="0" required>
                        @error('base_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_available" 
                                   name="is_available" {{ old('is_available', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_available">
                                Product is available for customers
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create Product
                        </button>
                        <a href="{{ route('business.products') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Tips</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Use a clear and descriptive product name</li>
                    <li class="mb-2">Include all important details in the description</li>
                    <li class="mb-2">Set a competitive base price</li>
                    <li>You can add customization options later</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
