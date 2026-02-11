@extends('layouts.business')

@section('title', 'Services - ' . $enterprise->name)
@section('page-title', 'Service Management')
@section('page-subtitle', 'Manage your services')

@section('header-actions')
<a href="{{ route('business.services.create') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth js-business-service-create">
    <i data-lucide="plus" class="h-4 w-4"></i>
    Add Service
</a>
@endsection

@section('content')

    @php
        $hasRequiresFileUpload = \Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload');
        $hasUploadEnabled = \Illuminate\Support\Facades\Schema::hasColumn('services', 'file_upload_enabled');
    @endphp

    <!-- Services Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 items-start">
        @forelse($services as $service)
            <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden">
                @if(!empty($service->image_path))
                    <div class="h-48 bg-secondary overflow-hidden">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($service->image_path) }}" alt="{{ $service->service_name }}" class="w-full h-full object-cover" />
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
                    
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('business.customizations.index', $service->service_id) }}"
                           class="px-3 py-2 text-xs sm:text-sm text-center border border-input rounded-md hover:bg-secondary transition-smooth whitespace-nowrap">
                            Customizations
                        </a>
                        <a href="{{ route('business.services.edit', $service->service_id) }}"
                           class="px-3 py-2 text-xs sm:text-sm text-center bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth js-business-service-edit whitespace-nowrap">
                            Edit
                        </a>

                        @if($hasRequiresFileUpload)
                            <button type="button"
                                    class="px-3 py-2 text-xs sm:text-sm border border-input rounded-md hover:bg-secondary transition-smooth whitespace-nowrap"
                                    onclick="openServiceUploadSettings('{{ $service->service_id }}', @json($service->service_name), {{ !empty($service->file_upload_enabled ?? false) ? 'true' : 'false' }}, {{ !empty($service->requires_file_upload) ? 'true' : 'false' }})">
                                Files
                            </button>
                        @else
                            <form id="delete-service-{{ $service->service_id }}" action="{{ route('business.services.delete', $service->service_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="w-full px-3 py-2 text-xs sm:text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90"
                                        onclick="confirmServiceDelete('{{ $service->service_id }}', @json($service->service_name))">
                                    <i data-lucide="trash-2" class="h-4 w-4 inline"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($hasRequiresFileUpload)
                        <div class="flex justify-end mt-2">
                            <form id="delete-service-{{ $service->service_id }}" action="{{ route('business.services.delete', $service->service_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="px-3 py-2 text-xs sm:text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90"
                                        onclick="confirmServiceDelete('{{ $service->service_id }}', @json($service->service_name))">
                                    <i data-lucide="trash-2" class="h-4 w-4 inline"></i>
                                </button>
                            </form>
                        </div>
                    @endif
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


    <x-ui.modal id="businessServiceFormModal" title="Service" size="xl" scrollable>
        <div id="businessServiceFormModalBody" class="min-h-[200px]"></div>
    </x-ui.modal>

    <x-modals.service-upload-settings />
    <x-modals.confirm-action />
@endsection

@push('scripts')
<script>
function openBusinessServiceFormModal(url) {
    const modalEl = document.getElementById('businessServiceFormModal');
    const bodyEl = document.getElementById('businessServiceFormModalBody');
    if (!modalEl || !bodyEl) {
        window.location.href = url;
        return;
    }

    const __upModalCache = (window.__upModalCache = window.__upModalCache || {});
    const cacheKey = `GET:${url}`;

    bodyEl.innerHTML = '<div class="py-5 text-center text-muted">Loading…</div>';

    let bsModal = window.modal_businessServiceFormModal;
    if (!bsModal && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalEl);
        window.modal_businessServiceFormModal = bsModal;
    }

    if (bsModal) {
        bsModal.show();
    }

    if (__upModalCache[cacheKey]) {
        bodyEl.innerHTML = __upModalCache[cacheKey];
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        return;
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        },
        credentials: 'same-origin'
    })
        .then((res) => {
            if (!res.ok) throw new Error('Failed to load service form');
            return res.text();
        })
        .then((html) => {
            __upModalCache[cacheKey] = html;
            bodyEl.innerHTML = html;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(() => {
            bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Failed to load service form.</div>';
        });
}

document.addEventListener('click', function (e) {
    const createLink = e.target.closest('a.js-business-service-create');
    if (createLink) {
        e.preventDefault();
        openBusinessServiceFormModal(createLink.getAttribute('href'));
        return;
    }

    const editLink = e.target.closest('a.js-business-service-edit');
    if (editLink) {
        e.preventDefault();
        openBusinessServiceFormModal(editLink.getAttribute('href'));
        return;
    }
});

document.addEventListener('submit', async function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.closest('#businessServiceFormModal')) return;

    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]');
    if (window.UniPrintUI && typeof window.UniPrintUI.setButtonLoading === 'function' && submitBtn) {
        window.UniPrintUI.setButtonLoading(submitBtn, true, { text: 'Saving…' });
    }

    try {
        const response = await fetch(form.action, {
            method: form.method ? form.method.toUpperCase() : 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        });

        const data = await response.json().catch(() => null);
        if (!response.ok || !data || data.success !== true) {
            throw new Error((data && data.message) ? data.message : 'Failed to save service');
        }

        if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
            window.UniPrintUI.toast(data.message || 'Saved.', { variant: 'success' });
        }

        const modalEl = document.getElementById('businessServiceFormModal');
        const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modal) modal.hide();

        setTimeout(() => window.location.reload(), 600);
    } catch (err) {
        if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
            window.UniPrintUI.toast(err.message || 'Failed to save service.', { variant: 'danger' });
        }
    } finally {
        if (window.UniPrintUI && typeof window.UniPrintUI.setButtonLoading === 'function' && submitBtn) {
            window.UniPrintUI.setButtonLoading(submitBtn, false);
        }
    }
}, true);

function openServiceUploadSettings(serviceId, serviceName, fileUploadEnabled, requiresFileUpload) {
    const modalEl = document.getElementById('serviceUploadSettingsModal');
    if (!modalEl) return;

    const nameEl = document.getElementById('serviceUploadSettingsName');
    const enabledEl = document.getElementById('serviceUploadSettingsEnabled');
    const requiresEl = document.getElementById('serviceUploadSettingsRequires');
    const formEl = document.getElementById('serviceUploadSettingsForm');

    if (nameEl) nameEl.textContent = serviceName || 'Service';
    if (enabledEl) enabledEl.checked = Boolean(fileUploadEnabled);
    if (requiresEl) requiresEl.checked = Boolean(requiresFileUpload);
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
