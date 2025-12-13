@props([
    'title' => '',
    'value' => '',
    'icon' => 'trending-up',
    'trend' => null,
    'trendType' => 'neutral',
    'color' => 'primary',
])

@php
    $gradientClasses = [
        'primary' => 'bg-gradient-to-br from-purple-500 to-purple-600',
        'accent' => 'bg-gradient-to-br from-orange-500 to-orange-600',
        'warning' => 'bg-gradient-to-br from-yellow-500 to-yellow-600',
        'success' => 'bg-gradient-to-br from-green-500 to-green-600',
        'admin' => 'bg-gradient-to-br from-red-500 to-red-600',
        'info' => 'bg-gradient-to-br from-blue-500 to-blue-600',
    ];
    
    $gradient = $gradientClasses[$color] ?? $gradientClasses['primary'];
@endphp

<div class="admin-stat-card {{ $gradient }} text-white {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    <div class="flex justify-between items-start mb-4">
        <div class="flex-1">
            <p class="text-white/70 text-sm mb-1">{{ $title }}</p>
            <h3 class="admin-stat-value">{{ $value }}</h3>
        </div>
        <div class="bg-white/20 p-3 rounded-lg">
            <i data-lucide="{{ $icon }}" class="h-6 w-6"></i>
        </div>
    </div>
    
    @if($trend)
        <div class="flex items-center gap-1 text-sm text-white/80">
            @if($trendType === 'up')
                <i data-lucide="trending-up" class="h-4 w-4"></i>
            @elseif($trendType === 'down')
                <i data-lucide="trending-down" class="h-4 w-4"></i>
            @else
                <i data-lucide="minus" class="h-4 w-4"></i>
            @endif
            <span>{{ $trend }}</span>
        </div>
    @endif
    
    @if($slot->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-white/20">
            {{ $slot }}
        </div>
    @endif
</div>
