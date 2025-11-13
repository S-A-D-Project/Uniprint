@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'variant' => 'default', // default, primary, success, warning, danger
    'size' => 'default', // sm, default, lg
    'hover' => true,
    'shadow' => true,
    'noPadding' => false,
    'headerActions' => null
])

@php
$classes = [
    'enhanced-card',
    'border',
    'rounded-lg',
    'overflow-hidden',
    'transition-smooth'
];

if ($hover) {
    $classes[] = 'hover-lift';
}

if ($shadow) {
    $classes[] = 'shadow-card';
}

$sizeClasses = [
    'sm' => 'card-sm',
    'default' => '',
    'lg' => 'card-lg'
];

$variantClasses = [
    'default' => 'bg-white border-gray-200',
    'primary' => 'bg-primary-50 border-primary-200',
    'success' => 'bg-success-50 border-success-200',
    'warning' => 'bg-warning-50 border-warning-200',
    'danger' => 'bg-danger-50 border-danger-200'
];

$classes[] = $sizeClasses[$size] ?? '';
$classes[] = $variantClasses[$variant] ?? $variantClasses['default'];
@endphp

<div {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
    @if($title || $icon || $headerActions)
    <div class="enhanced-card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            @if($icon)
                <i data-lucide="{{ $icon }}" class="h-5 w-5 text-primary"></i>
            @endif
            <div>
                @if($title)
                    <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
                @endif
                @if($subtitle)
                    <p class="text-muted mb-0 small">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if($headerActions)
            <div class="d-flex align-items-center gap-2">
                {{ $headerActions }}
            </div>
        @endif
    </div>
    @endif
    
    <div class="{{ $noPadding ? '' : 'enhanced-card-body' }}">
        {{ $slot }}
    </div>
</div>

@once
@push('styles')
<style>
.card-sm .enhanced-card-body {
    padding: 1rem;
}

.card-lg .enhanced-card-body {
    padding: 2rem;
}

.card-sm .enhanced-card-header {
    padding: 0.75rem 1rem;
}

.card-lg .enhanced-card-header {
    padding: 1.5rem 2rem;
}
</style>
@endpush
@endonce
