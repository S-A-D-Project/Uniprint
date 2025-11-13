@props([
    'text' => '',
    'position' => 'top',
    'trigger' => 'hover',
    'delay' => 0
])

@php
    $positions = [
        'top' => 'top',
        'bottom' => 'bottom',
        'left' => 'start',
        'right' => 'end'
    ];
    
    $placement = $positions[$position] ?? 'top';
@endphp

<span {{ $attributes->merge([
    'data-bs-toggle' => 'tooltip',
    'data-bs-placement' => $placement,
    'data-bs-title' => $text,
    'data-bs-trigger' => $trigger,
    'data-bs-delay' => $delay,
    'tabindex' => '0'
]) }}>
    {{ $slot }}
</span>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true,
            sanitize: false,
            customClass: 'custom-tooltip'
        });
    });
    
    // Handle keyboard accessibility
    tooltipTriggerList.forEach(function(element) {
        element.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const tooltip = bootstrap.Tooltip.getInstance(element);
                if (tooltip) {
                    tooltip.hide();
                }
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.custom-tooltip {
    --bs-tooltip-max-width: 300px;
    --bs-tooltip-padding-x: 0.75rem;
    --bs-tooltip-padding-y: 0.5rem;
    --bs-tooltip-font-size: 0.875rem;
    --bs-tooltip-bg: var(--bs-dark);
    --bs-tooltip-border-radius: 0.5rem;
}

.custom-tooltip .tooltip-inner {
    text-align: left;
    word-wrap: break-word;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .custom-tooltip {
        --bs-tooltip-bg: #000000;
        --bs-tooltip-color: #ffffff;
        border: 1px solid #ffffff;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .tooltip {
        transition: none !important;
    }
}

/* Focus styles for accessibility */
[data-bs-toggle="tooltip"]:focus {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}
</style>
@endpush
@endonce
