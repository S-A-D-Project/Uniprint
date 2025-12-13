@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
    'backdrop' => 'static',
    'keyboard' => true,
    'focus' => true,
    'show' => false,
    'centered' => false,
    'scrollable' => false,
    'fullscreen' => false
])

@php
    $sizes = [
        'xs' => 'modal-sm',
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl'
    ];
    
    $modalClasses = 'modal fade';
    $dialogClasses = 'modal-dialog ' . ($sizes[$size] ?? '');
    
    if ($centered) {
        $dialogClasses .= ' modal-dialog-centered';
    }
    
    if ($scrollable) {
        $dialogClasses .= ' modal-dialog-scrollable';
    }
    
    if ($fullscreen) {
        $dialogClasses .= ' modal-fullscreen';
    }
@endphp

<div class="{{ $modalClasses }}" 
     id="{{ $id }}" 
     tabindex="-1" 
     aria-labelledby="{{ $id }}Label" 
     aria-hidden="true"
     data-bs-backdrop="{{ $backdrop }}"
     @if(!$keyboard) data-bs-keyboard="false" @endif>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content border-0 shadow-lg">
            @if($title || isset($header))
                <div class="modal-header border-bottom-0 pb-0">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h5 class="modal-title fw-semibold" id="{{ $id }}Label">
                            {{ $title }}
                        </h5>
                    @endif
                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close">
                    </button>
                </div>
            @endif
            
            <div class="modal-body">
                {{ $slot }}
            </div>
            
            @if(isset($footer))
                <div class="modal-footer border-top-0 pt-0">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('{{ $id }}');
    if (!modal) return;
    
    const bsModal = new bootstrap.Modal(modal, {
        backdrop: '{{ $backdrop }}',
        keyboard: {{ $keyboard ? 'true' : 'false' }},
        focus: {{ $focus ? 'true' : 'false' }}
    });
    
    // Auto-show if specified
    @if($show)
        bsModal.show();
    @endif
    
    // Focus management for accessibility
    modal.addEventListener('shown.bs.modal', function() {
        const firstFocusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    });
    
    // Trap focus within modal
    modal.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            const focusableElements = modal.querySelectorAll(
                'input, button, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            if (e.shiftKey && document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            } else if (!e.shiftKey && document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    });
    
    // Store modal instance globally for programmatic access
    window[`modal_${('{{ $id }}').replace('-', '_')}`] = bsModal;
});
</script>
@endpush

<style>
.modal-content {
    border-radius: 12px;
}

.modal-header {
    padding: 1.5rem 1.5rem 0.75rem;
}

.modal-body {
    padding: 0.75rem 1.5rem;
}

.modal-footer {
    padding: 0.75rem 1.5rem 1.5rem;
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

@media (prefers-reduced-motion: reduce) {
    .modal.fade .modal-dialog {
        transition: none;
    }
}
</style>
