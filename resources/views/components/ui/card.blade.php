@props([
    'variant' => 'default',
    'padding' => 'default',
    'shadow' => 'default',
    'border' => true,
    'hover' => false
])

@php
    $baseClasses = 'bg-card text-card-foreground rounded-xl transition-all duration-200';
    
    $variants = [
        'default' => '',
        'primary' => 'bg-primary text-primary-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground',
        'success' => 'bg-success text-success-foreground',
        'warning' => 'bg-warning text-warning-foreground',
        'danger' => 'bg-destructive text-destructive-foreground',
        'gradient-primary' => 'bg-gradient-to-br from-primary to-primary/80 text-primary-foreground',
        'gradient-accent' => 'bg-gradient-to-br from-accent to-accent/80 text-accent-foreground'
    ];
    
    $paddings = [
        'none' => '',
        'sm' => 'p-3',
        'default' => 'p-4 lg:p-6',
        'lg' => 'p-6 lg:p-8',
        'xl' => 'p-8 lg:p-10'
    ];
    
    $shadows = [
        'none' => '',
        'sm' => 'shadow-sm',
        'default' => 'shadow-card',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl'
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $paddings[$padding] . ' ' . $shadows[$shadow];
    
    if ($border && $variant === 'default') {
        $classes .= ' border border-border';
    }
    
    if ($hover) {
        $classes .= ' hover:shadow-card-hover hover:-translate-y-1';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="mb-4 pb-4 border-b border-border/50">
            {{ $header }}
        </div>
    @endif
    
    @if(isset($title) || isset($description))
        <div class="mb-4">
            @if(isset($title))
                <h3 class="text-lg font-semibold mb-2">{{ $title }}</h3>
            @endif
            @if(isset($description))
                <p class="text-muted-foreground">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    <div class="{{ isset($content) ? 'card-content' : '' }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-border/50">
            {{ $footer }}
        </div>
    @endif
</div>
