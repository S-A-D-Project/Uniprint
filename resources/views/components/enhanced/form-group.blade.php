@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'icon' => null,
    'required' => false,
    'help' => null,
    'error' => null,
    'value' => null,
    'placeholder' => null,
    'options' => [], // for select
    'rows' => 3, // for textarea
    'size' => 'default', // sm, default, lg
    'variant' => 'default' // default, floating
])

@php
$inputId = $name ?? 'input_' . uniqid();
$hasError = $error || $errors->has($name);

$inputClasses = [
    'form-control',
    'form-control-enhanced'
];

if ($hasError) {
    $inputClasses[] = 'is-invalid';
}

$sizeClasses = [
    'sm' => 'form-control-sm',
    'lg' => 'form-control-lg'
];

if ($size !== 'default') {
    $inputClasses[] = $sizeClasses[$size] ?? '';
}
@endphp

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label fw-semibold">
            @if($icon)
                <i data-lucide="{{ $icon }}" class="h-4 w-4 me-1"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    @if($type === 'select')
        <select id="{{ $inputId }}" 
                name="{{ $name }}" 
                class="{{ implode(' ', $inputClasses) }}"
                @if($required) required @endif>
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" 
                        @if(old($name, $value) == $optionValue) selected @endif>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea id="{{ $inputId }}" 
                  name="{{ $name }}" 
                  class="{{ implode(' ', $inputClasses) }}"
                  rows="{{ $rows }}"
                  @if($placeholder) placeholder="{{ $placeholder }}" @endif
                  @if($required) required @endif>{{ old($name, $value) }}</textarea>
    @elseif($type === 'checkbox')
        <div class="form-check">
            <input type="checkbox" 
                   id="{{ $inputId }}" 
                   name="{{ $name }}" 
                   class="form-check-input"
                   value="1"
                   @if(old($name, $value)) checked @endif
                   @if($required) required @endif>
            <label class="form-check-label" for="{{ $inputId }}">
                {{ $label ?? $slot }}
            </label>
        </div>
    @elseif($type === 'switch')
        <div class="form-check form-switch">
            <input type="checkbox" 
                   id="{{ $inputId }}" 
                   name="{{ $name }}" 
                   class="form-check-input"
                   value="1"
                   @if(old($name, $value)) checked @endif
                   @if($required) required @endif>
            <label class="form-check-label fw-semibold" for="{{ $inputId }}">
                {{ $label ?? $slot }}
            </label>
        </div>
    @elseif($type === 'file')
        <input type="file" 
               id="{{ $inputId }}" 
               name="{{ $name }}" 
               class="form-control"
               @if($required) required @endif>
    @else
        @if($icon && $type !== 'checkbox' && $type !== 'switch')
            <div class="input-group">
                <span class="input-group-text">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
                </span>
                <input type="{{ $type }}" 
                       id="{{ $inputId }}" 
                       name="{{ $name }}" 
                       class="{{ implode(' ', $inputClasses) }}"
                       value="{{ old($name, $value) }}"
                       @if($placeholder) placeholder="{{ $placeholder }}" @endif
                       @if($required) required @endif>
            </div>
        @else
            <input type="{{ $type }}" 
                   id="{{ $inputId }}" 
                   name="{{ $name }}" 
                   class="{{ implode(' ', $inputClasses) }}"
                   value="{{ old($name, $value) }}"
                   @if($placeholder) placeholder="{{ $placeholder }}" @endif
                   @if($required) required @endif>
        @endif
    @endif
    
    @if($help)
        <div class="form-text">
            <i data-lucide="info" class="h-3 w-3 me-1"></i>
            {{ $help }}
        </div>
    @endif
    
    @if($hasError)
        <div class="invalid-feedback">
            {{ $error ?? $errors->first($name) }}
        </div>
    @endif
</div>
