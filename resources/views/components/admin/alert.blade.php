@props([
    'type' => 'info',
    'icon' => null,
    'dismissible' => false,
])

@php
    $typeClasses = [
        'success' => 'admin-alert-success',
        'warning' => 'admin-alert-warning',
        'error' => 'admin-alert-error',
        'info' => 'admin-alert-info',
    ];
    
    $defaultIcons = [
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'error' => 'alert-circle',
        'info' => 'info',
    ];
    
    $classes = 'admin-alert ' . ($typeClasses[$type] ?? $typeClasses['info']);
    $displayIcon = $icon ?? $defaultIcons[$type];
@endphp

<div class="{{ $classes }} {{ $attributes->get('class') }}" role="alert" {{ $attributes->except('class') }}>
    @if($displayIcon)
        <i data-lucide="{{ $displayIcon }}" class="h-5 w-5 flex-shrink-0"></i>
    @endif
    
    <div class="flex-1">
        {{ $slot }}
    </div>
    
    @if($dismissible)
        <button 
            type="button" 
            class="flex-shrink-0 p-1 hover:bg-black/5 rounded transition-smooth"
            onclick="this.parentElement.remove()"
            aria-label="Dismiss">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    @endif
</div>
