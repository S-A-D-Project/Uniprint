@props([
    'variant' => 'primary',
    'icon' => null,
])

@php
    $baseClasses = 'admin-badge';
    $variantClasses = [
        'primary' => 'admin-badge-primary',
        'secondary' => 'admin-badge-secondary',
        'success' => 'admin-badge-success',
        'warning' => 'admin-badge-warning',
        'destructive' => 'admin-badge-destructive',
        'info' => 'admin-badge-info',
        'outline' => 'admin-badge-outline',
    ];
    
    $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
@endphp

<span class="{{ $classes }} {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="h-3 w-3"></i>
    @endif
    {{ $slot }}
</span>
