@props([
    'id' => 'confirmActionModal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to perform this action?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'danger',
    'icon' => null
])

@php
    $variants = [
        'primary' => ['bg' => 'bg-primary', 'text' => 'text-primary-foreground', 'icon' => 'bi-info-circle'],
        'success' => ['bg' => 'bg-success', 'text' => 'text-success-foreground', 'icon' => 'bi-check-circle'],
        'warning' => ['bg' => 'bg-warning', 'text' => 'text-warning-foreground', 'icon' => 'bi-exclamation-triangle'],
        'danger' => ['bg' => 'bg-danger', 'text' => 'text-white', 'icon' => 'bi-exclamation-triangle-fill']
    ];
    
    $config = $variants[$variant] ?? $variants['danger'];
    $modalIcon = $icon ?? $config['icon'];
@endphp

<x-ui.modal :id="$id" :title="$title" size="sm" centered backdrop="static">
    <div class="text-center">
        @if($modalIcon)
            <div class="mb-4">
                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle {{ $config['bg'] }}/10" 
                     style="width: 64px; height: 64px;">
                    <i class="{{ $modalIcon }} {{ $config['bg'] === 'bg-danger' ? 'text-danger' : str_replace('bg-', 'text-', $config['bg']) }}" 
                       style="font-size: 1.5rem;"></i>
                </div>
            </div>
        @endif
        
        <div class="mb-4">
            <p class="text-muted mb-0">{{ $message }}</p>
        </div>
        
        @if(isset($details))
            <div class="alert alert-light text-start mb-4">
                {{ $details }}
            </div>
        @endif
    </div>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ $cancelText }}
        </button>
        <button type="button" class="btn {{ $config['bg'] }} {{ $config['text'] }}" id="{{ $id }}-confirm">
            <span class="btn-text">{{ $confirmText }}</span>
            <span class="btn-loading d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Processing...
            </span>
        </button>
    </x-slot>
</x-ui.modal>

@push('scripts')
<script>
class ConfirmActionModal {
    constructor(modalId) {
        this.modalId = modalId;
        this.modal = document.getElementById(modalId);
        this.bsModal = new bootstrap.Modal(this.modal);
        this.confirmBtn = document.getElementById(`${modalId}-confirm`);
        this.callback = null;
        this.context = null;
        
        this.init();
    }
    
    init() {
        // Reset state when modal is hidden
        this.modal.addEventListener('hidden.bs.modal', () => {
            this.reset();
        });
        
        // Handle confirm button click
        this.confirmBtn.addEventListener('click', () => {
            this.handleConfirm();
        });
        
        // Handle keyboard events
        this.modal.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !this.confirmBtn.disabled) {
                e.preventDefault();
                this.handleConfirm();
            }
        });
    }
    
    show(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to perform this action?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            callback = null,
            context = null
        } = options;
        
        // Update modal content
        const titleElement = this.modal.querySelector('.modal-title');
        const messageElement = this.modal.querySelector('.text-muted');
        const confirmTextElement = this.confirmBtn.querySelector('.btn-text');
        const cancelBtn = this.modal.querySelector('[data-bs-dismiss="modal"]');
        
        if (titleElement) titleElement.textContent = title;
        if (messageElement) messageElement.textContent = message;
        if (confirmTextElement) confirmTextElement.textContent = confirmText;
        if (cancelBtn) cancelBtn.textContent = cancelText;
        
        // Store callback and context
        this.callback = callback;
        this.context = context;
        
        // Show modal
        this.bsModal.show();
        
        // Focus confirm button after modal is shown
        this.modal.addEventListener('shown.bs.modal', () => {
            this.confirmBtn.focus();
        }, { once: true });
    }
    
    async handleConfirm() {
        if (!this.callback) {
            this.bsModal.hide();
            return;
        }
        
        // Show loading state
        this.setLoading(true);
        
        try {
            // Execute callback
            const result = await this.callback(this.context);
            
            // If callback returns false, don't close modal
            if (result !== false) {
                this.bsModal.hide();
            }
        } catch (error) {
            console.error('Confirmation callback error:', error);
            
            // Show error state
            this.showError('An error occurred. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    setLoading(loading) {
        const btnText = this.confirmBtn.querySelector('.btn-text');
        const btnLoading = this.confirmBtn.querySelector('.btn-loading');
        
        if (loading) {
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            this.confirmBtn.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
            this.confirmBtn.disabled = false;
        }
    }
    
    showError(message) {
        // Create or update error alert
        let errorAlert = this.modal.querySelector('.alert-danger');
        if (!errorAlert) {
            errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-sm mt-3';
            this.modal.querySelector('.modal-body').appendChild(errorAlert);
        }
        
        errorAlert.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
        `;
        
        // Remove error after 5 seconds
        setTimeout(() => {
            if (errorAlert && errorAlert.parentNode) {
                errorAlert.remove();
            }
        }, 5000);
    }
    
    reset() {
        this.callback = null;
        this.context = null;
        this.setLoading(false);
        
        // Remove any error alerts
        const errorAlert = this.modal.querySelector('.alert-danger');
        if (errorAlert) {
            errorAlert.remove();
        }
    }
    
    hide() {
        this.bsModal.hide();
    }
}

// Initialize the confirm modal
document.addEventListener('DOMContentLoaded', function() {
    window.confirmModal = new ConfirmActionModal('{{ $id }}');
});

// Global helper function
window.showConfirmModal = function(options) {
    if (window.confirmModal) {
        window.confirmModal.show(options);
    }
};
</script>
@endpush
