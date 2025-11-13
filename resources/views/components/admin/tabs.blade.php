@props([
    'id' => 'admin-tabs-' . uniqid(),
    'tabs' => [],
    'active' => 0,
])

<div {{ $attributes }}>
    <div class="admin-tabs" role="tablist">
        @foreach($tabs as $index => $tab)
            <button 
                class="admin-tab {{ $index === $active ? 'active' : '' }}"
                data-tab="{{ $id }}-{{ $index }}"
                onclick="switchAdminTab('{{ $id }}', {{ $index }})"
                role="tab"
                aria-selected="{{ $index === $active ? 'true' : 'false' }}"
                aria-controls="{{ $id }}-panel-{{ $index }}">
                @if(isset($tab['icon']))
                    <i data-lucide="{{ $tab['icon'] }}" class="h-4 w-4 inline mr-1"></i>
                @endif
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    
    <div class="admin-tab-content">
        {{ $slot }}
    </div>
</div>

@once
@push('scripts')
<script>
function switchAdminTab(tabsetId, tabIndex) {
    // Hide all panels for this tabset
    const panels = document.querySelectorAll(`[data-tabset="${tabsetId}"]`);
    panels.forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll(`[data-tab^="${tabsetId}-"]`);
    tabs.forEach(tab => {
        tab.classList.remove('active');
        tab.setAttribute('aria-selected', 'false');
    });
    
    // Show selected panel
    const selectedPanel = document.querySelector(`[data-tabset="${tabsetId}"][data-tab-index="${tabIndex}"]`);
    if (selectedPanel) {
        selectedPanel.classList.add('active');
    }
    
    // Activate selected tab
    const selectedTab = document.querySelector(`[data-tab="${tabsetId}-${tabIndex}"]`);
    if (selectedTab) {
        selectedTab.classList.add('active');
        selectedTab.setAttribute('aria-selected', 'true');
    }
    
    // Re-initialize Lucide icons for new content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script>
@endpush
@endonce
