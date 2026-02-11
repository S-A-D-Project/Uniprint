@extends('layouts.admin-layout')

@section('title', 'Enterprise Details')
@section('page-title', 'Enterprise Details')
@section('page-subtitle', 'Review enterprise performance, services, staff, and orders')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Enterprises', 'url' => route('admin.enterprises')],
    ['label' => 'Enterprise Details', 'url' => '#'],
];

$isActive = isset($enterprise->is_active) ? (bool) $enterprise->is_active : true;
$isVerified = isset($enterprise->is_verified) ? (bool) $enterprise->is_verified : true;
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card title="Enterprise" icon="building-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Enterprise ID</div>
                    <div class="font-mono font-semibold">{{ $enterprise->enterprise_id ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Status</div>
                    <div class="mt-1">
                        @if($isActive)
                            <x-admin.badge variant="success" icon="check-circle">Active</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="x-circle">Inactive</x-admin.badge>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Verification</div>
                    <div class="mt-1">
                        @if($isVerified)
                            <x-admin.badge variant="success" icon="check-circle">Verified</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="clock">Pending</x-admin.badge>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Name</div>
                    <div class="font-semibold">{{ $enterprise->name ?? 'Unknown Enterprise' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">TIN</div>
                    <div class="font-semibold">{{ $enterprise->tin_no ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Contact Person</div>
                    <div class="font-semibold">{{ $enterprise->contact_person ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Contact Number</div>
                    <div class="font-semibold">{{ $enterprise->contact_number ?? '—' }}</div>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_document_path'))
                <div class="mt-6 border-t border-border pt-5">
                    <div class="text-sm font-semibold mb-3">Verification Proof</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Submitted</div>
                            <div class="font-semibold">
                                @if(!empty($enterprise->verification_submitted_at))
                                    {{ is_string($enterprise->verification_submitted_at) ? date('M d, Y H:i', strtotime($enterprise->verification_submitted_at)) : \Carbon\Carbon::parse($enterprise->verification_submitted_at)->format('M d, Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Document</div>
                            <div class="font-semibold">
                                @if(!empty($enterprise->verification_document_path))
                                    <a class="text-primary hover:text-primary/80" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($enterprise->verification_document_path) }}" target="_blank" rel="noopener">View document</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_notes'))
                            <div class="md:col-span-2">
                                <div class="text-sm text-muted-foreground">Notes</div>
                                <div class="font-semibold whitespace-pre-line">{{ $enterprise->verification_notes ?? '—' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card title="Performance" icon="bar-chart-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Services</div>
                    <div class="text-2xl font-bold">{{ $stats['services_count'] ?? 0 }}</div>
                </div>
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Orders</div>
                    <div class="text-2xl font-bold">{{ $stats['orders_count'] ?? 0 }}</div>
                </div>
                <div class="p-4 bg-success/10 rounded-lg">
                    <div class="text-sm text-muted-foreground">Revenue</div>
                    <div class="text-2xl font-bold text-success">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                </div>
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Staff</div>
                    <div class="text-2xl font-bold">{{ $stats['staff_count'] ?? 0 }}</div>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Recent Orders" icon="shopping-cart" :noPadding="true">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="font-mono font-medium">#{{ $order->order_no ?? ($order->purchase_order_id ? substr($order->purchase_order_id, 0, 8) : 'N/A') }}</td>
                            <td>{{ $order->customer_name ?? 'Unknown' }}</td>
                            <td class="font-medium">₱{{ number_format($order->total ?? 0, 2) }}</td>
                            <td>
                                <x-admin.badge variant="secondary">{{ $order->status_name ?? 'Unknown' }}</x-admin.badge>
                            </td>
                            <td class="text-sm text-muted-foreground">
                                {{ isset($order->created_at) ? (is_string($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i')) : 'N/A' }}
                            </td>
                            <td>
                                <x-admin.button size="sm" variant="ghost" icon="eye" href="{{ route('admin.orders.details', $order->purchase_order_id) }}" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state icon="shopping-cart" title="No orders" description="This enterprise has no orders yet." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card title="Services" icon="package" :noPadding="true">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                        <tr>
                            <td class="font-medium">{{ $service->service_name ?? 'Service' }}</td>
                            <td>₱{{ number_format($service->base_price ?? 0, 2) }}</td>
                            <td>
                                @if(($service->is_available ?? true))
                                    <x-admin.badge variant="success" icon="check-circle">Available</x-admin.badge>
                                @else
                                    <x-admin.badge variant="secondary" icon="x-circle">Unavailable</x-admin.badge>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-admin.button size="sm" variant="ghost" icon="eye" href="{{ route('admin.services.details', $service->service_id) }}" />
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state icon="package" title="No services" description="This enterprise has no services yet." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card title="Staff" icon="users" :noPadding="true">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                        <tr>
                            <td class="font-medium">{{ $member->username ?? $member->name ?? 'Unknown' }}</td>
                            <td>{{ $member->email ?? '—' }}</td>
                            <td>{{ $member->position ?? '—' }}</td>
                            <td>{{ $member->department ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state icon="users" title="No staff" description="No staff members found for this enterprise." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Actions" icon="settings">
            <div class="space-y-2">
                @include('admin.partials.enterprise-verification-actions', ['enterprise' => $enterprise])
                <x-admin.button variant="outline" icon="shopping-cart" href="{{ route('admin.orders', ['enterprise_id' => $enterprise->enterprise_id]) }}" class="w-full">View All Enterprise Orders</x-admin.button>
                <x-admin.button variant="outline" icon="package" href="{{ route('admin.services', ['enterprise_id' => $enterprise->enterprise_id]) }}" class="w-full">View All Enterprise Services</x-admin.button>

                <form method="POST" action="{{ route('admin.enterprises.toggle-active', $enterprise->enterprise_id) }}">
                    @csrf
                    @if($isActive)
                        <x-admin.button type="submit" variant="destructive" icon="x-circle" class="w-full">Deactivate Enterprise</x-admin.button>
                    @else
                        <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Activate Enterprise</x-admin.button>
                    @endif
                </form>

                <x-admin.button variant="outline" icon="arrow-left" href="{{ route('admin.enterprises') }}" class="w-full">Back to Enterprises</x-admin.button>
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
