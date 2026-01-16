@props([
    'name' => '',
    'id' => null,
    'label' => '',
    'value' => '1',
    'checked' => false,
    'required' => false,
    'disabled' => false,
    'help' => '',
])

@php
    $inputId = $id;

    if (!$inputId) {
        $base = $name ?: ('checkbox_' . uniqid());
        $inputId = preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $base);
    }
@endphp

<div class="flex items-start gap-3">
    <input type="checkbox"
           id="{{ $inputId }}"
           name="{{ $name }}"
           value="{{ $value }}"
           @if($checked) checked @endif
           @if($required) required @endif
           @if($disabled) disabled @endif
           {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 shrink-0 rounded border border-input text-primary focus:ring-2 focus:ring-ring disabled:opacity-50 disabled:cursor-not-allowed']) }} />

    <div class="space-y-1">
        @if($label)
            <label for="{{ $inputId }}" class="text-sm font-medium leading-none">
                {{ $label }}
            </label>
        @endif

        @if($help)
            <p class="text-xs text-muted-foreground">{{ $help }}</p>
        @endif
    </div>
</div>
