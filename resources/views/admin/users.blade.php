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
                            <x-admin.button size="sm" variant="ghost" icon="edit-2" />
                            <x-admin.button size="sm" variant="ghost" icon="trash-2" />
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
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
