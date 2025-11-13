@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'disabled' => false,
])

@php
    $baseClasses = 'admin-btn';
    $variantClasses = [
        'primary' => 'admin-btn-primary',
        'secondary' => 'admin-btn-secondary',
        'outline' => 'admin-btn-outline',
        'ghost' => 'admin-btn-ghost',
        'destructive' => 'admin-btn-destructive',
        'success' => 'admin-btn-success',
    ];
    $sizeClasses = [
        'sm' => 'admin-btn-sm',
        'md' => 'admin-btn-md',
        'lg' => 'admin-btn-lg',
        'xl' => 'admin-btn-xl',
        'icon' => 'admin-btn-icon',
    ];
    
    $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }} {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }} {{ $attributes->get('class') }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->except('class') }}>
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
        @endif
    </button>
@endif
