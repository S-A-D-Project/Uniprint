@extends('layouts.business')

@section('title', 'Customizations - ' . $service->service_name)
@section('page-title', $service->service_name)
@section('page-subtitle', 'Manage customization options')

@section('header-actions')
<div class="flex gap-2">
    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth" data-bs-toggle="modal" data-bs-target="#addCustomizationModal">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Add Customization
    </button>
    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth" data-bs-toggle="modal" data-bs-target="#addCustomFieldModal">
        <i data-lucide="text-cursor-input" class="h-4 w-4"></i>
        Add Text Field
    </button>
    <a href="{{ route('business.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Back to Services
    </a>
</div>
@endsection

@section('content')

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Customizations List -->
        <div class="lg:col-span-2">
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h2 class="text-xl font-bold mb-4">Customization Options</h2>
                
                @php
                    $grouped = $customizations->groupBy('option_type');
                @endphp
                
                @if($grouped->isNotEmpty())
                    <div class="space-y-6">
                        @foreach($grouped as $type => $options)
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-lg">{{ $type }}</h3>
                                    <button type="button"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm border border-input rounded-md hover:bg-secondary transition-smooth"
                                            onclick="openAddCustomizationForType(@json($type))">
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                        Add option
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    @foreach($options as $option)
                                        <div class="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-secondary/30">
                                            <div>
                                                <p class="font-medium">{{ $option->option_name }}</p>
                                                <p class="text-sm text-muted-foreground">
                                                    Price modifier: 
                                                    @if($option->price_modifier >= 0)
                                                        <span class="text-primary">+₱{{ number_format($option->price_modifier, 2) }}</span>
                                                    @else
                                                        <span class="text-success">₱{{ number_format($option->price_modifier, 2) }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex gap-2">
                                                <x-ui.tooltip text="Edit this customization option">
                                                    <button type="button" class="px-3 py-1 text-sm bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80" onclick="editCustomization('{{ $option->option_id }}', '{{ $option->option_type }}', '{{ $option->option_name }}', '{{ $option->price_modifier }}')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </x-ui.tooltip>
                                                <x-ui.tooltip text="Delete this customization option">
                                                    <button type="button" class="px-3 py-1 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90" onclick="deleteCustomization('{{ $option->option_id }}', '{{ $option->option_name }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </x-ui.tooltip>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i data-lucide="sliders" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                        <p class="text-muted-foreground">No customization options yet</p>
                    </div>
                @endif
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6 mt-6">
                <h2 class="text-xl font-bold mb-4">Custom Text Fields</h2>

                @php
                    $fields = $customFields ?? collect();
                @endphp

                @if($fields->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($fields as $field)
                            <div class="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-secondary/30">
                                <div>
                                    <p class="font-medium">{{ $field->field_label }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ $field->is_required ? 'Required' : 'Optional' }}
                                        @if(!empty($field->placeholder))
                                            • Placeholder: {{ $field->placeholder }}
                                        @endif
                                        • Order: {{ $field->sort_order ?? 0 }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <x-ui.tooltip text="Edit this text field">
                                        <button type="button" class="px-3 py-1 text-sm bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80" onclick="editCustomField('{{ $field->field_id }}', '{{ $field->field_label }}', '{{ $field->placeholder ?? '' }}', {{ (int)($field->is_required ?? 0) }}, {{ (int)($field->sort_order ?? 0) }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </x-ui.tooltip>
                                    <x-ui.tooltip text="Delete this text field">
                                        <button type="button" class="px-3 py-1 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90" onclick="deleteCustomField('{{ $field->field_id }}', '{{ $field->field_label }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </x-ui.tooltip>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i data-lucide="text-cursor-input" class="h-16 w-16 mx-auto mb-4 text-muted-foreground"></i>
                        <p class="text-muted-foreground">No custom text fields yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div>
            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'supports_custom_size'))
                <div class="bg-card border border-border rounded-xl shadow-card p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Custom Size Settings</h2>

                    <form method="POST" action="{{ route('business.customizations.custom-size.update', $service->service_id) }}" class="space-y-4" data-up-global-loader>
                        @csrf
                        @method('PUT')

                        <x-ui.form.switch
                            name="supports_custom_size"
                            id="supports_custom_size"
                            :checked="old('supports_custom_size', !empty($service->supports_custom_size))"
                            label="Enable Custom Size"
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_unit'))
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="custom_size_unit">Unit</label>
                                    <select id="custom_size_unit"
                                            name="custom_size_unit"
                                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('custom_size_unit') border-destructive @enderror">
                                        @php
                                            $unitValue = old('custom_size_unit', $service->custom_size_unit ?? 'in');
                                            $unitValue = is_string($unitValue) && $unitValue !== '' ? strtolower($unitValue) : 'in';
                                        @endphp
                                        <option value="in" {{ $unitValue === 'in' ? 'selected' : '' }}>in</option>
                                        <option value="cm" {{ $unitValue === 'cm' ? 'selected' : '' }}>cm</option>
                                        <option value="mm" {{ $unitValue === 'mm' ? 'selected' : '' }}>mm</option>
                                    </select>
                                    @error('custom_size_unit')
                                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div class="hidden sm:block"></div>

                            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_min_width'))
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="custom_size_min_width">Min Width</label>
                                    <input type="number"
                                           id="custom_size_min_width"
                                           name="custom_size_min_width"
                                           value="{{ old('custom_size_min_width', $service->custom_size_min_width ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('custom_size_min_width') border-destructive @enderror" />
                                    @error('custom_size_min_width')
                                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_max_width'))
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="custom_size_max_width">Max Width</label>
                                    <input type="number"
                                           id="custom_size_max_width"
                                           name="custom_size_max_width"
                                           value="{{ old('custom_size_max_width', $service->custom_size_max_width ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('custom_size_max_width') border-destructive @enderror" />
                                    @error('custom_size_max_width')
                                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_min_height'))
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="custom_size_min_height">Min Height</label>
                                    <input type="number"
                                           id="custom_size_min_height"
                                           name="custom_size_min_height"
                                           value="{{ old('custom_size_min_height', $service->custom_size_min_height ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('custom_size_min_height') border-destructive @enderror" />
                                    @error('custom_size_min_height')
                                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            @if(\Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_max_height'))
                                <div>
                                    <label class="block text-sm font-medium mb-2" for="custom_size_max_height">Max Height</label>
                                    <input type="number"
                                           id="custom_size_max_height"
                                           name="custom_size_max_height"
                                           value="{{ old('custom_size_max_height', $service->custom_size_max_height ?? '') }}"
                                           step="0.01"
                                           min="0"
                                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring @error('custom_size_max_height') border-destructive @enderror" />
                                    @error('custom_size_max_height')
                                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth" data-up-button-loader>
                            Save Custom Size Settings
                        </button>
                    </form>
                </div>
            @endif

            <x-ui.collapsible title="Customization Statistics" icon="bi bi-bar-chart" :expanded="true">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-primary/5 rounded-lg">
                        <div class="text-2xl font-bold text-primary">{{ $customizations->count() }}</div>
                        <div class="text-sm text-muted-foreground">Total Options</div>
                    </div>
                    <div class="text-center p-3 bg-success/5 rounded-lg">
                        <div class="text-2xl font-bold text-success">{{ $grouped->count() ?? 0 }}</div>
                        <div class="text-sm text-muted-foreground">Option Types</div>
                    </div>
                </div>
                
                @if($customizations->count() > 0)
                <div class="mt-4 pt-4 border-t border-border/50">
                    <h4 class="font-semibold mb-2">Price Range</h4>
                    <div class="text-sm text-muted-foreground">
                        <div>Lowest: ₱{{ number_format($customizations->min('price_modifier'), 2) }}</div>
                        <div>Highest: ₱{{ number_format($customizations->max('price_modifier'), 2) }}</div>
                    </div>
                </div>
                @endif
            </x-ui.collapsible>
        </div>
    </div>
</div>

<!-- Add Customization Modal -->
<x-ui.modal id="addCustomizationModal" title="Add Customization Option" size="md" centered>
    <form id="addCustomizationForm" action="{{ route('business.customizations.store', $service->service_id) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Option Type *</label>
                @php
                    $existingTypes = $customizations
                        ->pluck('option_type')
                        ->filter()
                        ->map(fn($t) => trim((string) $t))
                        ->filter(fn($t) => $t !== '')
                        ->unique()
                        ->sort()
                        ->values();
                @endphp

                @if($existingTypes->count() > 1)
                    <select name="option_type_select" id="add_option_type_select"
                            class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="" disabled selected>Select a type</option>
                        @foreach($existingTypes as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                        <option value="__new__">New type...</option>
                    </select>

                    <input type="text" name="option_type_new" id="add_option_type_new" placeholder="e.g., Size, Color, Paper" disabled
                           class="w-full mt-2 px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">

                    <input type="hidden" name="option_type" id="add_option_type" value="" required>
                @else
                    <input type="text" name="option_type" id="add_option_type" placeholder="e.g., Size, Color, Paper" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                @endif
                <small class="text-muted-foreground">Group similar options together (e.g., all sizes under "Size")</small>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Option Name *</label>
                <input type="text" name="option_name" placeholder="e.g., A4, Red, Glossy" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Price Modifier (₱) *</label>
                <input type="number" name="price_modifier" step="0.01" value="0" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                <small class="text-muted-foreground">Use negative values for discounts (e.g., -5.00)</small>
            </div>
        </div>
    </form>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addCustomizationForm" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Option
        </button>
    </x-slot>
</x-ui.modal>

<x-ui.modal id="addCustomFieldModal" title="Add Custom Text Field" size="md" centered>
    <form id="addCustomFieldForm" action="{{ route('business.custom-fields.store', $service->service_id) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Field Label *</label>
                <input type="text" name="field_label" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Placeholder</label>
                <input type="text" name="placeholder"
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Required?</label>
                <input type="checkbox" name="is_required" value="1" class="h-4 w-4 rounded">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0" max="1000"
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addCustomFieldForm" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Field
        </button>
    </x-slot>
</x-ui.modal>

<!-- Edit Customization Modal -->
<x-ui.modal id="editCustomizationModal" title="Edit Customization Option" size="md" centered>
    <form id="editCustomizationForm" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Option Type *</label>
                <input type="text" name="option_type" id="edit_option_type" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Option Name *</label>
                <input type="text" name="option_name" id="edit_option_name" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Price Modifier (₱) *</label>
                <input type="number" name="price_modifier" id="edit_price_modifier" step="0.01" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
        </div>
    </form>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="editCustomizationForm" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Update Option
        </button>
    </x-slot>
</x-ui.modal>

<x-ui.modal id="editCustomFieldModal" title="Edit Custom Text Field" size="md" centered>
    <form id="editCustomFieldForm" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Field Label *</label>
                <input type="text" name="field_label" id="edit_field_label" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Placeholder</label>
                <input type="text" name="placeholder" id="edit_placeholder"
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Required?</label>
                <input type="checkbox" name="is_required" id="edit_is_required" value="1" class="h-4 w-4 rounded">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Sort Order</label>
                <input type="number" name="sort_order" id="edit_sort_order" value="0" min="0" max="1000"
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="editCustomFieldForm" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Update Field
        </button>
    </x-slot>
</x-ui.modal>

<!-- Confirmation Modal -->
<x-modals.confirm-action />

@endsection

@push('scripts')
<script>
function syncAddOptionTypeHidden() {
    const select = document.getElementById('add_option_type_select');
    const hidden = document.getElementById('add_option_type');
    const inputNew = document.getElementById('add_option_type_new');
    if (!hidden) return;

    if (!select || !inputNew) {
        return;
    }

    const selected = select.value;
    if (selected === '__new__') {
        inputNew.disabled = false;
        inputNew.required = true;
        hidden.value = inputNew.value || '';
    } else {
        inputNew.disabled = true;
        inputNew.required = false;
        inputNew.value = '';
        hidden.value = selected || '';
    }
}

function openAddCustomizationForType(optionType) {
    const select = document.getElementById('add_option_type_select');
    const hidden = document.getElementById('add_option_type');
    const inputNew = document.getElementById('add_option_type_new');
    const raw = optionType || '';

    if (select && hidden && inputNew) {
        const hasMatching = Array.from(select.options).some(o => o.value === raw);
        if (hasMatching) {
            select.value = raw;
        } else {
            select.value = '__new__';
            inputNew.value = raw;
        }
        syncAddOptionTypeHidden();
    } else if (hidden) {
        hidden.value = raw;
    }

    const modalEl = document.getElementById('addCustomizationModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();

    setTimeout(() => {
        const nameInput = modalEl.querySelector('input[name="option_name"]');
        if (nameInput) nameInput.focus();
    }, 150);
}

function editCustomization(optionId, optionType, optionName, priceModifier) {
    // Populate form fields
    document.getElementById('edit_option_type').value = optionType;
    document.getElementById('edit_option_name').value = optionName;
    document.getElementById('edit_price_modifier').value = priceModifier;
    
    // Set form action
    const form = document.getElementById('editCustomizationForm');
    form.action = `{{ route('business.customizations.update', [$service->service_id, ':optionId']) }}`.replace(':optionId', optionId);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editCustomizationModal'));
    modal.show();
}

function editCustomField(fieldId, fieldLabel, placeholder, isRequired, sortOrder) {
    document.getElementById('edit_field_label').value = fieldLabel;
    document.getElementById('edit_placeholder').value = placeholder;
    document.getElementById('edit_is_required').checked = Boolean(isRequired);
    document.getElementById('edit_sort_order').value = sortOrder;

    const form = document.getElementById('editCustomFieldForm');
    form.action = `{{ route('business.custom-fields.update', [$service->service_id, ':fieldId']) }}`.replace(':fieldId', fieldId);

    const modal = new bootstrap.Modal(document.getElementById('editCustomFieldModal'));
    modal.show();
}

function deleteCustomField(fieldId, fieldLabel) {
    showConfirmModal({
        title: 'Delete Custom Text Field',
        message: `Are you sure you want to delete the "${fieldLabel}" field? This action cannot be undone.`,
        confirmText: 'Delete Field',
        variant: 'danger',
        callback: async () => {
            try {
                const response = await fetch(`{{ route('business.custom-fields.delete', [$service->service_id, ':fieldId']) }}`.replace(':fieldId', fieldId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    showToast('Custom field deleted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error('Failed to delete field');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Failed to delete custom field. Please try again.', 'error');
                return false;
            }
        }
    });
}

function deleteCustomization(optionId, optionName) {
    showConfirmModal({
        title: 'Delete Customization Option',
        message: `Are you sure you want to delete the "${optionName}" option? This action cannot be undone.`,
        confirmText: 'Delete Option',
        variant: 'danger',
        callback: async () => {
            try {
                const response = await fetch(`{{ route('business.customizations.delete', [$service->service_id, ':optionId']) }}`.replace(':optionId', optionId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    showToast('Customization option deleted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error('Failed to delete option');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Failed to delete customization option. Please try again.', 'error');
                return false; // Keep modal open
            }
        }
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Initialize Lucide icons when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('add_option_type_select');
    const inputNew = document.getElementById('add_option_type_new');
    const form = document.getElementById('addCustomizationForm');
    if (select) {
        select.addEventListener('change', syncAddOptionTypeHidden);
    }
    if (inputNew) {
        inputNew.addEventListener('input', syncAddOptionTypeHidden);
    }
    if (form) {
        form.addEventListener('submit', function () {
            syncAddOptionTypeHidden();
        });
    }
});
</script>
@endpush
