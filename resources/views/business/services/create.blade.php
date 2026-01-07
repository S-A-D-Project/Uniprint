@extends('layouts.business')

@section('title', 'Create Service')
@section('page-title', 'Create New Service')
@section('page-subtitle', 'Add a new service to your catalog')

@section('sidebar')
    <a href="{{ route('business.dashboard') }}" class="nav-link">
        <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('business.services.index') }}" class="nav-link active">
        <i data-lucide="package" class="h-5 w-5"></i>
        <span>Services</span>
    </a>
    <a href="{{ route('business.orders.index') }}" class="nav-link">
        <i data-lucide="shopping-bag" class="h-5 w-5"></i>
        <span>Orders</span>
    </a>
    <a href="{{ route('business.pricing.index') }}" class="nav-link">
        <i data-lucide="peso-sign" class="h-5 w-5"></i>
        <span>Pricing</span>
    </a>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.services.index') }}">Services</a></li>
                <li class="breadcrumb-item active">Create Service</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('business.services.index') }}" class="btn btn-outline-secondary">
        <i data-lucide="arrow-left" class="h-4 w-4 me-2"></i>
        Back to Services
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="enhanced-card">
            <div class="enhanced-card-header">
                <h4 class="mb-0">
                    <i data-lucide="plus-circle" class="h-5 w-5 me-2 text-primary"></i>
                    Create New Service
                </h4>
                <p class="text-muted mb-0 mt-1">Fill in the details below to add a new service to your catalog</p>
            </div>
            <div class="enhanced-card-body">
                <form action="{{ route('business.services.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="service_name" class="form-label fw-semibold">
                                <i data-lucide="tag" class="h-4 w-4 me-1"></i>
                                Service Name *
                            </label>
                            <input type="text" 
                                   class="form-control form-control-enhanced @error('service_name') is-invalid @enderror" 
                                   id="service_name" 
                                   name="service_name" 
                                   value="{{ old('service_name') }}" 
                                   placeholder="Enter service name"
                                   required>
                            @error('service_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="base_price" class="form-label fw-semibold">
                                <i data-lucide="peso-sign" class="h-4 w-4 me-1"></i>
                                Base Price (₱) *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       class="form-control form-control-enhanced @error('base_price') is-invalid @enderror" 
                                       id="base_price" 
                                       name="base_price" 
                                       value="{{ old('base_price') }}" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00"
                                       required>
                                @error('base_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            <i data-lucide="file-text" class="h-4 w-4 me-1"></i>
                            Service Description
                        </label>
                        <textarea class="form-control form-control-enhanced @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Describe your service features, specifications, and benefits...">{{ old('description') }}</textarea>
                        <div class="form-text">
                            <i data-lucide="info" class="h-3 w-3 me-1"></i>
                            A detailed description helps customers understand your service better
                        </div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">
                                        <i data-lucide="settings" class="h-4 w-4 me-1"></i>
                                        Service Settings
                                    </h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                        <label class="form-check-label fw-semibold" for="is_active">
                                            Service is Active
                                        </label>
                                        <div class="form-text">Active services are visible to customers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">
                                        <i data-lucide="info" class="h-4 w-4 me-1"></i>
                                        Quick Tips
                                    </h6>
                                    <ul class="list-unstyled mb-0 small text-muted">
                                        <li><i data-lucide="check" class="h-3 w-3 me-1 text-success"></i>Use clear, descriptive names</li>
                                        <li><i data-lucide="check" class="h-3 w-3 me-1 text-success"></i>Set competitive pricing</li>
                                        <li><i data-lucide="check" class="h-3 w-3 me-1 text-success"></i>Include detailed descriptions</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="{{ route('business.services.index') }}" class="btn btn-outline-secondary">
                            <i data-lucide="x" class="h-4 w-4 me-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-enhanced-primary">
                            <i data-lucide="plus-circle" class="h-4 w-4 me-2"></i>
                            Create Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    
    // Initialize Lucide icons
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
@endsection
