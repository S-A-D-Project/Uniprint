@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'footer' => null,
    'hover' => false,
    'noPadding' => false,
])

@php
    $classes = 'admin-card';
    if ($hover) {
        $classes .= ' admin-card-hover';
    }
@endphp

<div class="{{ $classes }} {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    @if($title || $icon)
        <div class="admin-card-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($icon)
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <i data-lucide="{{ $icon }}" class="h-5 w-5 text-primary"></i>
                        </div>
                    @endif
                    <div>
                        @if($title)
                            <h3 class="text-lg font-semibold">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-muted-foreground">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @isset($actions)
                    <div class="flex items-center gap-2">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    @endif
    
    <div class="{{ $noPadding ? '' : 'admin-card-body' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="admin-card-footer">
            {{ $footer }}
        </div>
    @endif
    
    @isset($customFooter)
        <div class="admin-card-footer">
            {{ $customFooter }}
        </div>
    @endisset
</div>
