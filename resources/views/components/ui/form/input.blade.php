@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => '',
    'icon' => null,
    'iconPosition' => 'left',
    'size' => 'md'
])

@php
    $inputId = $name ? $name : 'input_' . uniqid();
    
    $baseClasses = 'w-full border border-input bg-background text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizes = [
        'sm' => 'h-8 px-3 text-sm rounded-md',
        'md' => 'h-10 px-3 text-sm rounded-lg',
        'lg' => 'h-11 px-4 text-base rounded-lg'
    ];
    
    $classes = $baseClasses . ' ' . $sizes[$size];
    
    if ($error) {
        $classes .= ' border-destructive focus:ring-destructive';
    }
    
    if ($icon) {
        if ($iconPosition === 'left') {
            $classes .= ' pl-10';
        } else {
            $classes .= ' pr-10';
        }
    }
@endphp

<div class="space-y-2">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-foreground">
            {{ $label }}
            @if($required)
                <span class="text-destructive">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} h-4 w-4 text-muted-foreground"></i>
            </div>
        @endif
        
        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ old($name, $value) }}"
               placeholder="{{ $placeholder }}"
               @if($required) required @endif
               @if($disabled) disabled @endif
               @if($readonly) readonly @endif
               {{ $attributes->merge(['class' => $classes]) }} />
        
        @if($icon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} h-4 w-4 text-muted-foreground"></i>
            </div>
        @endif
    </div>
    
    @if($error)
        <p class="text-sm text-destructive flex items-center">
            <i class="bi bi-exclamation-circle mr-1"></i>
            {{ $error }}
        </p>
    @elseif($help)
        <p class="text-sm text-muted-foreground">{{ $help }}</p>
    @endif
</div>
