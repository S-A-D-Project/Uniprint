@props([
    'variant' => 'primary', // primary, secondary, success, warning, danger, outline-primary, outline-secondary, etc.
    'size' => 'default', // sm, default, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'type' => 'button'
])

@php
$baseClasses = [
    'btn',
    'btn-enhanced',
    'd-inline-flex',
    'align-items-center',
    'justify-content-center',
    'gap-2',
    'transition-smooth'
];

$variantClasses = [
    'primary' => 'btn-enhanced-primary',
    'secondary' => 'btn-outline-secondary',
    'success' => 'btn-success',
    'warning' => 'btn-warning',
    'danger' => 'btn-danger',
    'outline-primary' => 'btn-outline-primary',
    'outline-secondary' => 'btn-outline-secondary',
    'outline-success' => 'btn-outline-success',
    'outline-warning' => 'btn-outline-warning',
    'outline-danger' => 'btn-outline-danger',
    'ghost' => 'btn-ghost'
];

$sizeClasses = [
    'sm' => 'btn-sm',
    'default' => '',
    'lg' => 'btn-lg'
];

$classes = array_merge($baseClasses, [
    $variantClasses[$variant] ?? $variantClasses['primary'],
    $sizeClasses[$size] ?? ''
]);

if ($disabled || $loading) {
    $classes[] = 'disabled';
}

$iconSize = match($size) {
    'sm' => 'h-3 w-3',
    'lg' => 'h-5 w-5',
    default => 'h-4 w-4'
};
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
        @if($loading)
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" 
            {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}
            @if($disabled || $loading) disabled @endif>
        @if($loading)
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </button>
@endif

@once
@push('styles')
<style>
.btn-ghost {
    background: transparent;
    border: 1px solid transparent;
    color: var(--bs-body-color);
}

.btn-ghost:hover {
    background-color: var(--bs-secondary);
    color: var(--bs-primary);
}

.btn-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-enhanced:hover::before {
    left: 100%;
}
</style>
@endpush
@endonce
