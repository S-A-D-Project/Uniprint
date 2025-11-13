@extends('layouts.app')

@section('title', 'My Design Assets')

@section('dashboard-route', route('customer.dashboard'))

@section('sidebar')
    <a href="{{ route('customer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('customer.enterprises') }}" class="nav-link">
        <i class="bi bi-shop"></i>Browse Shops
    </a>
    <a href="{{ route('customer.orders') }}" class="nav-link">
        <i class="bi bi-bag"></i>My Orders
    </a>
    <a href="{{ route('customer.design-assets') }}" class="nav-link active">
        <i class="bi bi-images"></i>My Designs
    </a>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0"><i class="bi bi-images me-2"></i>My Design Assets</h2>
        <p class="text-muted mb-0">Manage your uploaded designs and artwork</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDesignModal">
        <i class="bi bi-cloud-upload me-2"></i>Upload Design
    </button>
</div>

<div class="row g-4">
    @forelse($assets as $asset)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-light rounded p-4">
                        <i class="bi bi-file-earmark-image text-primary" style="font-size: 3rem;"></i>
                    </div>
                </div>

                <h5 class="card-title text-center mb-2">{{ $asset->asset_name }}</h5>
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Uploaded {{ $asset->created_at->diffForHumans() }}
                    </small>
                </div>

                @if($asset->storage_path)
                <div class="mt-3">
                    <small class="text-muted d-block">File Path:</small>
                    <small class="font-monospace">{{ Str::limit($asset->storage_path, 30) }}</small>
                </div>
                @endif

                <div class="mt-3 d-grid gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadAsset('{{ $asset->asset_id }}', '{{ $asset->asset_name }}')">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="previewAsset('{{ $asset->asset_id }}', '{{ $asset->asset_name }}')">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-images"></i>
                    <h5>No design assets yet</h5>
                    <p class="text-muted mb-3">Upload your designs to use them in your orders</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDesignModal">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Your First Design
                    </button>
                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Upload your designs to use them in future orders
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($assets->hasPages())
<div class="mt-4">
    {{ $assets->links() }}
</div>
@endif

<!-- Upload Design Modal -->
<x-modals.upload-design />

<!-- Asset Preview Modal -->
<x-ui.modal id="assetPreviewModal" title="Asset Preview" size="lg" centered>
    <div id="previewContent" class="text-center">
        <!-- Preview content will be loaded here -->
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="downloadFromPreview">
            <i class="bi bi-download me-2"></i>Download
        </button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
async function downloadAsset(assetId, assetName) {
    try {
        const response = await fetch(`/api/design-assets/${assetId}/download`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Download failed');
        }
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = assetName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('Asset downloaded successfully!', 'success');
    } catch (error) {
        console.error('Download error:', error);
        showToast('Failed to download asset. Please try again.', 'error');
    }
}

async function previewAsset(assetId, assetName) {
    const modal = new bootstrap.Modal(document.getElementById('assetPreviewModal'));
    const previewContent = document.getElementById('previewContent');
    const downloadBtn = document.getElementById('downloadFromPreview');
    
    // Show loading state
    previewContent.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Set download button action
    downloadBtn.onclick = () => downloadAsset(assetId, assetName);
    
    modal.show();
    
    try {
        const response = await fetch(`/api/design-assets/${assetId}/preview`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Preview failed');
        }
        
        const data = await response.json();
        
        if (data.type === 'image') {
            previewContent.innerHTML = `
                <div class="mb-3">
                    <h5>${assetName}</h5>
                </div>
                <img src="${data.preview_url}" class="img-fluid rounded" alt="${assetName}" style="max-height: 400px;">
                <div class="mt-3 text-muted">
                    <small>File size: ${data.file_size} | Dimensions: ${data.dimensions || 'N/A'}</small>
                </div>
            `;
        } else {
            previewContent.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5>${assetName}</h5>
                    <p class="text-muted">Preview not available for this file type</p>
                    <small class="text-muted">File size: ${data.file_size}</small>
                </div>
            `;
        }
    } catch (error) {
        console.error('Preview error:', error);
        previewContent.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-warning mb-3" style="font-size: 4rem;"></i>
                <h5>Preview Error</h5>
                <p class="text-muted">Unable to load preview for this asset</p>
            </div>
        `;
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>
@endpush
