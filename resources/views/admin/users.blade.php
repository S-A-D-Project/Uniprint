@extends('layouts.admin-layout')

@section('title', 'Users Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all system users')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Users', 'url' => '#'],
];
@endphp

@section('header-actions')
    <div class="flex items-center gap-2">
        <div class="text-sm text-muted-foreground">
            Total: {{ $users->total() }} users
        </div>
        <x-admin.button variant="default" icon="user-plus" size="sm" onclick="openCreateUserModal()">
            Add User
        </x-admin.button>
        <x-admin.button variant="outline" icon="refresh-cw" size="sm" onclick="location.reload()">
            Refresh
        </x-admin.button>
        <x-admin.button variant="outline" icon="download" size="sm">
            Export
        </x-admin.button>
        <x-admin.button variant="outline" icon="filter" size="sm">
            Filter
        </x-admin.button>
    </div>
@endsection

@section('content')
<x-admin.card title="All Users" icon="users" :noPadding="true">
    <x-slot:actions>
        <x-admin.button size="sm" variant="ghost" icon="refresh-cw">
            Refresh
        </x-admin.button>
    </x-slot:actions>
    
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Enterprise</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="font-semibold">{{ $user->user_id ?? 'N/A' }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <i data-lucide="user" class="h-4 w-4 text-muted-foreground"></i>
                            {{ $user->username ?? $user->name ?? 'Unknown' }}
                        </div>
                    </td>
                    <td>{{ $user->email ?? 'N/A' }}</td>
                    <td>
                        @if(($user->role_type ?? '') == 'admin')
                            <x-admin.badge variant="destructive">Admin</x-admin.badge>
                        @elseif(($user->role_type ?? '') == 'business_user')
                            <x-admin.badge variant="primary">Business User</x-admin.badge>
                        @else
                            <x-admin.badge variant="success">Customer</x-admin.badge>
                        @endif
                    </td>
                    <td>
                        @if(($user->is_active ?? true))
                            <x-admin.badge variant="success" icon="check-circle">Active</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="x-circle">Inactive</x-admin.badge>
                        @endif
                    </td>
                    <td>
                        @if(!empty($user->enterprise_name))
                            {{ $user->enterprise_name }}
                        @else
                            <span class="text-muted-foreground">—</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted-foreground">
                        {{ isset($user->created_at) ? (is_string($user->created_at) ? date('M d, Y', strtotime($user->created_at)) : $user->created_at->format('M d, Y')) : 'N/A' }}
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-ui.tooltip text="View user details">
                                <x-admin.button size="sm"
                                               variant="ghost"
                                               icon="eye"
                                               href="{{ route('admin.users.details', $user->user_id) }}"
                                               class="js-user-details"
                                               data-user-details-url="{{ route('admin.users.details', $user->user_id) }}" />
                            </x-ui.tooltip>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <x-admin.empty-state 
                            icon="users"
                            title="No users found"
                            description="No users have been registered yet" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <x-slot:customFooter>
        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    </x-slot:customFooter>
    @endif
</x-admin.card>

<x-ui.modal id="userDetailsModal" title="User Details" size="xl" scrollable>
    <div id="userDetailsModalBody" class="min-h-[200px]"></div>
</x-ui.modal>

<x-ui.modal id="createUserModal" title="Add New User" size="lg" scrollable>
    <div id="createUserModalBody" class="min-h-[200px]"></div>
</x-ui.modal>
@endsection

@push('scripts')
<script>
    function openCreateUserModal() {
        const modalEl = document.getElementById('createUserModal');
        const bodyEl = document.getElementById('createUserModalBody');
        if (!modalEl || !bodyEl) return;

        const url = "{{ route('admin.users.create') }}";
        bodyEl.innerHTML = '<div class="py-10 text-center text-muted-foreground">Loading form…</div>';

        let bsModal = window.modal_createUserModal;
        if (!bsModal && typeof bootstrap !== 'undefined') {
            bsModal = new bootstrap.Modal(modalEl);
            window.modal_createUserModal = bsModal;
        }
        if (bsModal) bsModal.show();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(res => res.text())
        .then(html => {
            bodyEl.innerHTML = html;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            bodyEl.innerHTML = '<div class="alert alert-danger p-4">Failed to load form.</div>';
        });
    }

    lucide.createIcons();

    const __upModalCache = (window.__upModalCache = window.__upModalCache || {});

    function __cleanupBootstrapModalState() {
        try {
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            const upLoading = document.querySelector('.up-loading-overlay');
            if (upLoading) {
                upLoading.classList.remove('up-loading-overlay--show');
                upLoading.remove();
            }

            const upModalRoot = document.querySelector('.up-modal-root');
            if (upModalRoot) {
                upModalRoot.querySelectorAll('.up-modal-wrapper').forEach((el) => el.remove());
            }
            document.documentElement.classList.remove('up-no-scroll');
        } catch (e) {
            // ignore
        }
    }

    function openUserDetailsModal(url) {
        const modalEl = document.getElementById('userDetailsModal');
        const bodyEl = document.getElementById('userDetailsModalBody');
        if (!modalEl || !bodyEl) return;

        if (!modalEl.dataset.upCleanupBound) {
            modalEl.dataset.upCleanupBound = '1';
            modalEl.addEventListener('hidden.bs.modal', function () {
                __cleanupBootstrapModalState();
            });
        }

        modalEl.dataset.currentDetailsUrl = url;
        const cacheKey = `GET:${url}`;

        let modal = window.modal_userDetailsModal;
        if (!modal && typeof bootstrap !== 'undefined') {
            modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            window.modal_userDetailsModal = modal;
        }

        if (modal && !modalEl.classList.contains('show')) {
            modal.show();
        }

        if (__upModalCache[cacheKey]) {
            bodyEl.innerHTML = __upModalCache[cacheKey];
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }

        bodyEl.innerHTML = '<div class="p-4"></div>';

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            credentials: 'same-origin'
        })
        .then(async (res) => {
            if (!res.ok) throw new Error('Failed to load');
            return await res.text();
        })
        .then((html) => {
            const next = html || '<div class="p-4 text-muted">No content</div>';
            __upModalCache[cacheKey] = next;
            bodyEl.innerHTML = next;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch((err) => {
            console.error(err);
            bodyEl.innerHTML = '<div class="p-4 text-danger">Failed to load user details.</div>';
        })
        .finally(() => {
            __cleanupBootstrapModalState();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-user-details').forEach((a) => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = a.getAttribute('data-user-details-url') || a.getAttribute('href');
                if (!url) return;
                openUserDetailsModal(url);
            });
        });

        const modalEl = document.getElementById('userDetailsModal');
        const bodyEl = document.getElementById('userDetailsModalBody');
        if (!modalEl || !bodyEl) return;

        bodyEl.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (href && href === `{{ route('admin.users') }}`) {
                e.preventDefault();
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });

        bodyEl.addEventListener('submit', async function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }

            const url = form.getAttribute('action');
            if (!url) return;

            try {
                const res = await fetch(url, {
                    method: (form.getAttribute('method') || 'POST').toUpperCase(),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                const data = await res.json().catch(() => null);
                if (!res.ok || !data || data.success !== true) {
                    const text = !data ? await res.text().catch(() => '') : '';
                    throw new Error(data?.message || text || 'Request failed');
                }

                if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                    window.UniPrintUI.toast(data.message || 'Updated.', { variant: 'success' });
                }

                const isDelete = url.includes('/delete');
                if (isDelete) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.reload();
                } else {
                    const currentUrl = modalEl.dataset.currentDetailsUrl;
                    if (currentUrl) {
                        delete __upModalCache[`GET:${currentUrl}`];
                        openUserDetailsModal(currentUrl);
                    }
                }
            } catch (err) {
                console.error(err);
                if (window.UniPrintUI && typeof window.UniPrintUI.toast === 'function') {
                    window.UniPrintUI.toast('Failed to update user.', { variant: 'danger' });
                }
            } finally {
                __cleanupBootstrapModalState();

                try {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modalEl.classList.contains('show') && modal) {
                        modal.handleUpdate();
                    }
                } catch (e) {
                    // ignore
                }
            }
        }, true);
    });
</script>
@endpush
