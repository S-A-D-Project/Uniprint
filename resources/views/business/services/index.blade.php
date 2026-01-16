@extends('layouts.business')

@section('title', 'Services - ' . $enterprise->name)
@section('page-title', 'Service Management')
@section('page-subtitle', 'Manage your services')

@section('header-actions')
<a href="{{ route('business.services.create') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
    <i data-lucide="plus" class="h-4 w-4"></i>
    Add Service
</a>
@endsection

@section('content')

    @php
        $hasRequiresFileUpload = \Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload');
    @endphp

    <!-- Services Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
            <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden">
                @if(!empty($service->image_path))
                    <div class="h-48 bg-secondary overflow-hidden">
                        <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="w-full h-full object-cover" />
                    </div>
                @else
                    <div class="h-48 gradient-accent flex items-center justify-center">
                        <i data-lucide="package" class="h-24 w-24 text-white"></i>
                    </div>
                @endif
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-lg">{{ $service->service_name }}</h3>
                        @if($service->is_active)
                            <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md">Active</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs bg-destructive/10 text-destructive rounded-md">Inactive</span>
                        @endif
                    </div>
                    
                    <p class="text-muted-foreground text-sm mb-4 line-clamp-2">
                        {{ $service->description ?? 'No description' }}
                    </p>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Base Price</p>
                            <p class="text-2xl font-bold text-primary">₱{{ number_format($service->base_price, 2) }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-border">
                        <div>
                            <p class="text-xs text-muted-foreground">Orders</p>
                            <p class="font-semibold">{{ $service->order_count }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Customizations</p>
                            <p class="font-semibold">{{ $service->customization_count }}</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('business.customizations.index', $service->service_id) }}" 
                           class="flex-1 px-3 py-2 text-sm text-center border border-input rounded-md hover:bg-secondary transition-smooth">
                            Customizations
                        </a>
                        <a href="{{ route('business.services.edit', $service->service_id) }}" 
                           class="flex-1 px-3 py-2 text-sm text-center bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                            Edit
                        </a>

                        @if($hasRequiresFileUpload)
                            <button type="button"
                                    class="px-3 py-2 text-sm border border-input rounded-md hover:bg-secondary transition-smooth"
                                    onclick="openServiceUploadSettings('{{ $service->service_id }}', @json($service->service_name), {{ !empty($service->requires_file_upload) ? 'true' : 'false' }})">
                                Files
                            </button>
                        @endif

                        <form id="toggle-service-{{ $service->service_id }}" action="{{ route('business.services.toggle-status', $service->service_id) }}" method="POST">
                            @csrf
                            <button type="button"
                                    class="px-3 py-2 text-sm {{ $service->is_active ? 'bg-secondary text-secondary-foreground' : 'bg-success text-white' }} rounded-md hover:opacity-90"
                                    onclick="confirmServiceToggleStatus('{{ $service->service_id }}', @json($service->service_name), {{ $service->is_active ? 'true' : 'false' }})">
                                {{ $service->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form id="delete-service-{{ $service->service_id }}" action="{{ route('business.services.delete', $service->service_id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="px-3 py-2 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90"
                                    onclick="confirmServiceDelete('{{ $service->service_id }}', @json($service->service_name))">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-card border border-border rounded-xl shadow-card p-12 text-center">
                <i data-lucide="package" class="h-24 w-24 mx-auto mb-4 text-muted-foreground"></i>
                <h3 class="text-xl font-bold mb-2">No Services Yet</h3>
                <p class="text-muted-foreground mb-6">Start by adding your first service</p>
                <a href="{{ route('business.services.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Add Service
                </a>
            </div>
        @endforelse
    </div>
    
    @if($services->hasPages())
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif
</div>

    <x-modals.service-upload-settings />
    <x-modals.confirm-action />
@endsection

@push('scripts')
<script>
function openServiceUploadSettings(serviceId, serviceName, requiresFileUpload) {
    const modalEl = document.getElementById('serviceUploadSettingsModal');
    if (!modalEl) return;

    const nameEl = document.getElementById('serviceUploadSettingsName');
    const checkboxEl = document.getElementById('serviceUploadSettingsRequires');
    const formEl = document.getElementById('serviceUploadSettingsForm');

    if (nameEl) nameEl.textContent = serviceName || 'Service';
    if (checkboxEl) checkboxEl.checked = Boolean(requiresFileUpload);
    if (formEl) {
        formEl.action = `{{ route('business.services.upload-settings', ':id') }}`.replace(':id', serviceId);
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const formEl = document.getElementById('serviceUploadSettingsForm');
    if (!formEl) return;

    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();

        try {
            const response = await fetch(formEl.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(formEl)
            });

            if (!response.ok) {
                throw new Error('Failed to save');
            }

            if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                window.UniPrintUI.toast('File upload settings updated.', { variant: 'success' });
            }

            const modalEl = document.getElementById('serviceUploadSettingsModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            console.error(err);
            if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                window.UniPrintUI.toast('Failed to update file upload settings.', { variant: 'danger' });
            }
        }
    });
});

function confirmServiceDelete(serviceId, serviceName) {
    if (typeof window.showConfirmModal !== 'function') return;
    showConfirmModal({
        title: 'Delete Service',
        message: `Are you sure you want to delete "${serviceName}"? This action cannot be undone.`,
        confirmText: 'Delete',
        variant: 'danger',
        callback: async () => {
            document.getElementById(`delete-service-${serviceId}`).submit();
        }
    });
}

function confirmServiceToggleStatus(serviceId, serviceName, currentlyActive) {
    if (typeof window.showConfirmModal !== 'function') return;

    const actionWord = currentlyActive ? 'deactivate' : 'activate';
    showConfirmModal({
        title: 'Confirm Action',
        message: `Are you sure you want to ${actionWord} "${serviceName}"?`,
        confirmText: currentlyActive ? 'Deactivate' : 'Activate',
        variant: 'warning',
        callback: async () => {
            document.getElementById(`toggle-service-${serviceId}`).submit();
        }
    });
}
</script>
@endpush
