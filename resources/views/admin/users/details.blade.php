@extends('layouts.admin-layout')

@section('title', 'User Details')
@section('page-title', 'User Details')
@section('page-subtitle', 'Review user information and account status')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Users', 'url' => route('admin.users')],
    ['label' => 'User Details', 'url' => '#'],
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card title="Profile" icon="user">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">User ID</div>
                    <div class="font-mono font-semibold">{{ $user->user_id ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Role</div>
                    <div class="font-semibold">{{ $user->role_type ?? 'Unknown' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Username</div>
                    <div class="font-semibold">{{ $user->username ?? $user->name ?? 'Unknown' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Email</div>
                    <div class="font-semibold">{{ $user->email ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Enterprise</div>
                    <div class="font-semibold">
                        @if(!empty($user->enterprise_id))
                            <a href="{{ route('admin.enterprises.details', $user->enterprise_id) }}" class="text-primary hover:underline">
                                {{ $user->enterprise_name ?? 'Enterprise' }}
                            </a>
                        @else
                            <span class="text-muted-foreground">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Created</div>
                    <div class="font-semibold">
                        {{ isset($user->created_at) ? (is_string($user->created_at) ? date('M d, Y H:i', strtotime($user->created_at)) : $user->created_at->format('M d, Y H:i')) : 'N/A' }}
                    </div>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Customer Activity" icon="shopping-cart">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Orders</div>
                    <div class="text-2xl font-bold">{{ $orderCount ?? 0 }}</div>
                </div>
                <div class="p-4 bg-success/10 rounded-lg">
                    <div class="text-sm text-muted-foreground">Total Spent</div>
                    <div class="text-2xl font-bold text-success">₱{{ number_format($orderTotal ?? 0, 2) }}</div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Status" icon="shield-check">
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-muted-foreground">Account Status</div>
                    <div class="mt-1">
                        @if(($user->is_active ?? true))
                            <x-admin.badge variant="success" icon="check-circle">Active</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="x-circle">Inactive</x-admin.badge>
                        @endif
                    </div>
                </div>

                @if(($hasUserIsActive ?? false))
                    <form method="POST" action="{{ route('admin.users.toggle-active', $user->user_id) }}">
                        @csrf
                        @if(($user->is_active ?? true))
                            <x-admin.button type="submit" variant="destructive" icon="x-circle" class="w-full">Deactivate User</x-admin.button>
                        @else
                            <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Activate User</x-admin.button>
                        @endif
                    </form>
                @endif

                <x-admin.button variant="outline" icon="arrow-left" href="{{ route('admin.users') }}" class="w-full">Back to Users</x-admin.button>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
