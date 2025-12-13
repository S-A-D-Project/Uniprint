@props([
    'type' => 'info',
    'title' => '',
    'dismissible' => false,
    'icon' => null
])

@php
    $types = [
        'success' => [
            'classes' => 'bg-success/10 border-success/20 text-success-foreground',
            'icon' => 'bi bi-check-circle-fill',
            'iconColor' => 'text-success'
        ],
        'error' => [
            'classes' => 'bg-destructive/10 border-destructive/20 text-destructive-foreground',
            'icon' => 'bi bi-exclamation-triangle-fill',
            'iconColor' => 'text-destructive'
        ],
        'warning' => [
            'classes' => 'bg-warning/10 border-warning/20 text-warning-foreground',
            'icon' => 'bi bi-exclamation-triangle-fill',
            'iconColor' => 'text-warning'
        ],
        'info' => [
            'classes' => 'bg-blue-50 border-blue-200 text-blue-800',
            'icon' => 'bi bi-info-circle-fill',
            'iconColor' => 'text-blue-600'
        ]
    ];
    
    $config = $types[$type];
    $alertIcon = $icon ?? $config['icon'];
    
    $baseClasses = 'relative p-4 border rounded-lg transition-all duration-300';
    $classes = $baseClasses . ' ' . $config['classes'];
    
    if ($dismissible) {
        $classes .= ' pr-12';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    <div class="flex items-start">
        @if($alertIcon)
            <div class="flex-shrink-0 mr-3">
                <i class="{{ $alertIcon }} {{ $config['iconColor'] }}"></i>
            </div>
        @endif
        
        <div class="flex-1 min-w-0">
            @if($title)
                <h4 class="font-medium mb-1">{{ $title }}</h4>
            @endif
            
            <div class="{{ $title ? 'text-sm' : '' }}">
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <button type="button" 
                    class="absolute top-4 right-4 text-current hover:text-current/80 transition-colors"
                    onclick="this.parentElement.style.display='none'">
                <i class="bi bi-x text-lg"></i>
                <span class="sr-only">Dismiss</span>
            </button>
        @endif
    </div>
</div>

@if($dismissible)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss after 5 seconds if dismissible
            setTimeout(function() {
                const alert = document.querySelector('[role="alert"]');
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    </script>
@endif
