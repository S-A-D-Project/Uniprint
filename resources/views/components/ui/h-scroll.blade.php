@props([
    'id' => null,
    'class' => '',
    'scrollerClass' => '',
    'showControls' => true,
])

@php
    $uid = $id ?: 'up_hscroll_' . substr(md5(uniqid('', true)), 0, 10);
@endphp

<div {{ $attributes->merge(['class' => trim($class)]) }} id="{{ $uid }}">
    {{ $slot }}
</div>
