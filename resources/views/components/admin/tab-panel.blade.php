@props([
    'tabset' => '',
    'index' => 0,
    'active' => false,
])

<div 
    class="admin-tab-panel {{ $active ? 'active' : '' }}"
    data-tabset="{{ $tabset }}"
    data-tab-index="{{ $index }}"
    role="tabpanel"
    {{ $attributes }}>
    {{ $slot }}
</div>
