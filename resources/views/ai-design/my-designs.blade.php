@extends('layouts.public')

@section('title', 'My Designs')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0"><i class="bi bi-images me-2"></i>My Designs</h2>
        <p class="text-muted mb-0">Your AI-generated design library</p>
    </div>
    <a href="{{ route('ai-design.index') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create New Design
    </a>
</div>

@if($designs->count() > 0)
    <div class="row g-4">
        @foreach($designs as $design)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100" data-design-id="{{ $design->design_id }}" data-title="{{ e($design->title) }}" data-url="{{ e($design->file_url) }}" data-description="{{ e($design->description ?? '') }}" data-date="{{ e(\Carbon\Carbon::parse($design->created_at)->format('M d, Y')) }}">
                <div class="position-relative">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <div class="position-absolute top-0 end-0 p-2">
                        <button class="btn btn-sm btn-light rounded-circle" onclick="deleteDesign('{{ $design->design_id }}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="card-title">{{ $design->title }}</h6>
                    <p class="card-text text-muted small">{{ Str::limit($design->description, 100) }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            {{ \Carbon\Carbon::parse($design->created_at)->format('M d, Y') }}
                        </small>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewDesign('{{ $design->design_id }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="downloadDesign('{{ $design->design_id }}')">
                                <i class="bi bi-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($designs->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $designs->links() }}
    </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-images text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 mb-2">No designs yet</h4>
        <p class="text-muted mb-4">Start creating amazing designs with our AI tool</p>
        <a href="{{ route('ai-design.index') }}" class="btn btn-primary">
            <i class="bi bi-magic me-2"></i>Create Your First Design
        </a>
    </div>
@endif

<!-- Design Preview Modal -->
<div class="modal fade" id="designModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="designModalTitle">Design Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="designModalImage" class="img-fluid rounded" alt="Design">
                <div class="mt-3">
                    <h6 id="designModalDescription"></h6>
                    <small class="text-muted" id="designModalDate"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="downloadModalBtn">
                    <i class="bi bi-download me-2"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// View design in modal
function viewDesign(designId) {
    const card = document.querySelector(`[data-design-id="${designId}"]`);
    if (!card) return;

    const title = card.dataset.title || 'Design Preview';
    const imageUrl = card.dataset.url || '';
    const description = card.dataset.description || '';
    const date = card.dataset.date || '';

    document.getElementById('designModalTitle').textContent = title;
    document.getElementById('designModalImage').src = imageUrl;
    document.getElementById('designModalDescription').textContent = description;
    document.getElementById('designModalDate').textContent = date;
    document.getElementById('downloadModalBtn').onclick = () => downloadDesign(designId);

    const modal = new bootstrap.Modal(document.getElementById('designModal'));
    modal.show();
}

// Download design
function downloadDesign(designId) {
    window.open(`/api/designs/${designId}/download`, '_blank');
}

// Delete design
function deleteDesign(designId) {
    if (confirm('Are you sure you want to delete this design? This action cannot be undone.')) {
        fetch(`/api/designs/${designId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Design deleted successfully', 'success');
                // Remove the card from DOM
                const wrapper = document.querySelector(`[data-design-id="${designId}"]`)?.closest('.col-md-6');
                if (wrapper) {
                    wrapper.remove();
                }
                
                // Check if no designs left
                if (document.querySelectorAll('.col-md-6').length === 0) {
                    location.reload();
                }
            } else {
                showToast(data.message || 'Failed to delete design', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error. Please try again.', 'error');
        });
    }
}

// Toast notification helper
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'success'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    setTimeout(() => {
        document.body.removeChild(toastContainer);
    }, 5000);
}
</script>
@endpush
