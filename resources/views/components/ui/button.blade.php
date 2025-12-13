@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'target' => null,
    'ariaLabel' => null,
    'ariaDescribedBy' => null,
    'tooltip' => null,
    'fullWidth' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80 focus:ring-secondary',
        'success' => 'bg-success text-success-foreground hover:bg-success/90 focus:ring-success',
        'warning' => 'bg-warning text-warning-foreground hover:bg-warning/90 focus:ring-warning',
        'danger' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90 focus:ring-destructive',
        'outline' => 'border border-border bg-background text-foreground hover:bg-muted focus:ring-ring',
        'ghost' => 'text-foreground hover:bg-muted focus:ring-ring',
        'link' => 'text-primary underline-offset-4 hover:underline focus:ring-primary'
    ];
    
    $sizes = [
        'xs' => 'h-7 px-2 text-xs rounded-md',
        'sm' => 'h-8 px-3 text-sm rounded-md',
        'md' => 'h-10 px-4 text-sm rounded-lg',
        'lg' => 'h-11 px-6 text-base rounded-lg',
        'xl' => 'h-12 px-8 text-lg rounded-xl'
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
    
    if ($loading) {
        $classes .= ' pointer-events-none';
    }
    
    if ($fullWidth) {
        $classes .= ' w-full';
    }
    
    // Accessibility attributes
    $ariaAttributes = [];
    if ($ariaLabel) {
        $ariaAttributes['aria-label'] = $ariaLabel;
    }
    if ($ariaDescribedBy) {
        $ariaAttributes['aria-describedby'] = $ariaDescribedBy;
    }
    if ($loading) {
        $ariaAttributes['aria-busy'] = 'true';
    }
    if ($disabled) {
        $ariaAttributes['aria-disabled'] = 'true';
    }
@endphp

@php
    $buttonContent = function() use ($loading, $icon, $iconPosition, $slot) {
        $content = '';
        
        if ($loading) {
            $content .= '<svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>';
        } elseif ($icon && $iconPosition === 'left') {
            $content .= '<i class="' . $icon . ' mr-2" aria-hidden="true"></i>';
        }
        
        $content .= $slot;
        
        if ($icon && $iconPosition === 'right') {
            $content .= '<i class="' . $icon . ' ml-2" aria-hidden="true"></i>';
        }
        
        return $content;
    };
@endphp

@if($tooltip)
    <x-ui.tooltip :text="$tooltip">
@endif

@if($href)
    <a href="{{ $href }}" 
       @if($target) target="{{ $target }}" @endif
       @foreach($ariaAttributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
       {{ $attributes->merge(['class' => $classes]) }}>
        {!! $buttonContent() !!}
    </a>
@else
    <button type="{{ $type }}" 
            @if($disabled || $loading) disabled @endif
            @foreach($ariaAttributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
            {{ $attributes->merge(['class' => $classes]) }}>
        {!! $buttonContent() !!}
    </button>
@endif

@if($tooltip)
    </x-ui.tooltip>
@endif
