@props([
    'id' => 'collapsible-' . uniqid(),
    'title' => '',
    'icon' => null,
    'expanded' => false,
    'variant' => 'default',
    'size' => 'md'
])

@php
    $variants = [
        'default' => 'bg-card border border-border',
        'primary' => 'bg-primary/5 border border-primary/20',
        'secondary' => 'bg-secondary border border-secondary',
        'success' => 'bg-success/5 border border-success/20',
        'warning' => 'bg-warning/5 border border-warning/20',
        'danger' => 'bg-destructive/5 border border-destructive/20'
    ];
    
    $sizes = [
        'sm' => 'text-sm',
        'md' => '',
        'lg' => 'text-lg'
    ];
    
    $containerClasses = 'rounded-lg overflow-hidden ' . ($variants[$variant] ?? $variants['default']);
    $headerClasses = 'p-4 cursor-pointer transition-colors hover:bg-muted/50 ' . ($sizes[$size] ?? '');
    $contentClasses = 'border-t border-border/50';
@endphp

<div class="{{ $containerClasses }}" {{ $attributes }}>
    <!-- Header -->
    <div class="{{ $headerClasses }}" 
         data-bs-toggle="collapse" 
         data-bs-target="#{{ $id }}" 
         aria-expanded="{{ $expanded ? 'true' : 'false' }}" 
         aria-controls="{{ $id }}"
         role="button"
         tabindex="0"
         onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); this.click(); }">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @if($icon)
                    <i class="{{ $icon }} me-3"></i>
                @endif
                <h6 class="mb-0 fw-semibold">{{ $title }}</h6>
            </div>
            <i class="bi bi-chevron-down transition-transform" id="{{ $id }}-chevron"></i>
        </div>
    </div>
    
    <!-- Content -->
    <div class="collapse {{ $expanded ? 'show' : '' }}" id="{{ $id }}">
        <div class="{{ $contentClasses }}">
            <div class="p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const collapsible = document.getElementById('{{ $id }}');
    const chevron = document.getElementById('{{ $id }}-chevron');
    
    if (collapsible && chevron) {
        collapsible.addEventListener('show.bs.collapse', function() {
            chevron.style.transform = 'rotate(180deg)';
        });
        
        collapsible.addEventListener('hide.bs.collapse', function() {
            chevron.style.transform = 'rotate(0deg)';
        });
        
        // Set initial state
        if ({{ $expanded ? 'true' : 'false' }}) {
            chevron.style.transform = 'rotate(180deg)';
        }
    }
});
</script>
@endpush

<style>
.transition-transform {
    transition: transform 0.3s ease;
}

.collapsible-header:hover {
    background-color: rgba(var(--bs-muted-rgb), 0.1);
}

.collapsible-content {
    transition: all 0.3s ease;
}

@media (prefers-reduced-motion: reduce) {
    .transition-transform,
    .collapsible-content {
        transition: none;
    }
}
</style>
