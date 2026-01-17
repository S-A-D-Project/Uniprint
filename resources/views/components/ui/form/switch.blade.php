@props([
    'name' => '',
    'id' => null,
    'label' => '',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'help' => '',
])

@php
    $inputId = $id;

    if (!$inputId) {
        $base = $name ?: ('switch_' . uniqid());
        $inputId = preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $base);
    }
@endphp

<div class="flex items-center justify-between gap-4">
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

    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $value }}"
               @if($checked) checked @endif
               @if($disabled) disabled @endif
               class="sr-only peer" />
        <div class="w-11 h-6 bg-muted peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-ring rounded-full peer peer-checked:bg-primary peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
        <div class="absolute left-1 top-1 w-4 h-4 bg-background rounded-full transition-transform peer-checked:translate-x-5"></div>
    </label>
</div>
