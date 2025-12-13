@props([
    'size' => 'md',
    'color' => 'primary',
    'text' => '',
    'overlay' => false
])

@php
    $sizes = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
        'xl' => 'h-12 w-12'
    ];
    
    $colors = [
        'primary' => 'text-primary',
        'secondary' => 'text-secondary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-destructive',
        'white' => 'text-white',
        'current' => 'text-current'
    ];
    
    $spinnerSize = $sizes[$size];
    $spinnerColor = $colors[$color];
@endphp

@if($overlay)
    <div class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-card p-6 rounded-lg shadow-lg border border-border">
            <div class="flex flex-col items-center space-y-4">
                <svg class="animate-spin {{ $spinnerSize }} {{ $spinnerColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @if($text)
                    <p class="text-sm text-muted-foreground">{{ $text }}</p>
                @endif
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}>
        <div class="flex items-center space-x-2">
            <svg class="animate-spin {{ $spinnerSize }} {{ $spinnerColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            @if($text)
                <span class="text-sm {{ $spinnerColor }}">{{ $text }}</span>
            @endif
        </div>
    </div>
@endif

@if($slot->isNotEmpty())
    <div class="mt-4">
        {{ $slot }}
    </div>
@endif
