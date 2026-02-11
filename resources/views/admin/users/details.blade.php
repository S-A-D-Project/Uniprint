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
                @if(!empty($user->enterprise_id) && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'is_verified'))
                    @php
                        $enterpriseIsVerified = isset($user->enterprise_is_verified) ? (bool) $user->enterprise_is_verified : true;
                    @endphp
                    <div>
                        <div class="text-sm text-muted-foreground">Enterprise Verification</div>
                        <div class="mt-1">
                            @if($enterpriseIsVerified)
                                <x-admin.badge variant="success" icon="check-circle">Verified</x-admin.badge>
                            @else
                                <x-admin.badge variant="secondary" icon="clock">Pending</x-admin.badge>
                            @endif
                        </div>
                    </div>
                @endif
                <div>
                    <div class="text-sm text-muted-foreground">Created</div>
                    <div class="font-semibold">
                        {{ isset($user->created_at) ? (is_string($user->created_at) ? date('M d, Y H:i', strtotime($user->created_at)) : $user->created_at->format('M d, Y H:i')) : 'N/A' }}
                    </div>
                </div>
            </div>
        </x-admin.card>

        @if(!empty($user->enterprise_id) && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_document_path'))
            <x-admin.card title="Verification Proof" icon="file-check">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-muted-foreground">Submitted</div>
                        <div class="font-semibold">
                            @if(!empty($user->enterprise_verification_submitted_at))
                                {{ is_string($user->enterprise_verification_submitted_at) ? date('M d, Y H:i', strtotime($user->enterprise_verification_submitted_at)) : \Carbon\Carbon::parse($user->enterprise_verification_submitted_at)->format('M d, Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-muted-foreground">Document</div>
                        <div class="font-semibold">
                            @if(!empty($user->enterprise_verification_document_path))
                                <a class="text-primary hover:text-primary/80" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->enterprise_verification_document_path) }}" target="_blank" rel="noopener">View document</a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                @if(isset($enterpriseIsVerified) && !$enterpriseIsVerified)
                    <div class="mt-4">
                        @include('admin.partials.enterprise-verification-actions', ['enterprise' => (object) ['enterprise_id' => $user->enterprise_id, 'is_verified' => $enterpriseIsVerified]])
                    </div>
                @endif
            </x-admin.card>
        @endif

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

                @if(($user->role_type ?? '') !== 'admin' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_enabled'))
                    @php
                        $emailTwoFactorEnabled = (bool) ($user->two_factor_enabled ?? false);
                    @endphp
                    <div>
                        <div class="text-sm text-muted-foreground">Email 2FA</div>
                        <div class="mt-1">
                            @if($emailTwoFactorEnabled)
                                <x-admin.badge variant="success" icon="check-circle">Enabled</x-admin.badge>
                            @else
                                <x-admin.badge variant="secondary" icon="x-circle">Disabled</x-admin.badge>
                            @endif
                        </div>
                    </div>
                @endif

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

                @if(($user->role_type ?? '') !== 'admin' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_enabled'))
                    @php
                        $emailTwoFactorEnabled = (bool) ($user->two_factor_enabled ?? false);
                    @endphp
                    @if($emailTwoFactorEnabled)
                        <form method="POST" action="{{ route('admin.users.disable-email-2fa', $user->user_id) }}">
                            @csrf
                            <x-admin.button type="submit" variant="outline" icon="shield-off" class="w-full">Disable Email 2FA</x-admin.button>
                        </form>
                    @endif
                @endif

                @if(($user->role_type ?? '') !== 'admin')
                    <form method="POST" action="{{ route('admin.users.delete', $user->user_id) }}" onsubmit="return confirm('Delete this user permanently? This cannot be undone.');">
                        @csrf
                        <x-admin.button type="submit" variant="destructive" icon="trash-2" class="w-full">Delete User</x-admin.button>
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
