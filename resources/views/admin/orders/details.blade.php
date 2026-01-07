@extends('layouts.admin-layout')

@section('title', 'Order Details')
@section('page-title', 'Order Details')
@section('page-subtitle', 'Review order information and update status')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Orders', 'url' => route('admin.orders')],
    ['label' => 'Order Details', 'url' => '#'],
];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card title="Order Summary" icon="shopping-cart">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Order #</div>
                    <div class="font-mono font-semibold">#{{ $order->order_no ?? ($order->purchase_order_id ? substr($order->purchase_order_id, 0, 8) : 'N/A') }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Status</div>
                    <div class="font-semibold">{{ $order->status_name ?? 'Unknown' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Customer</div>
                    <div class="font-semibold">{{ $order->customer_name ?? 'Unknown' }}</div>
                    <div class="text-sm text-muted-foreground">{{ $order->customer_email ?? '' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Enterprise</div>
                    <div class="font-semibold">{{ $order->enterprise_name ?? 'Unknown' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Created</div>
                    <div class="font-semibold">{{ isset($order->created_at) ? date('M d, Y H:i', strtotime($order->created_at)) : 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Total</div>
                    <div class="text-lg font-bold text-success">₱{{ number_format($order->total ?? 0, 2) }}</div>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Order Items" icon="package" :noPadding="true">
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Total</th>
                            <th>Customizations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderItems as $item)
                        <tr>
                            <td class="font-medium">{{ $item->service_name ?? 'Service' }}</td>
                            <td>{{ $item->quantity ?? 0 }}</td>
                            <td>₱{{ number_format($item->unit_price ?? 0, 2) }}</td>
                            <td class="font-medium">₱{{ number_format($item->total_cost ?? 0, 2) }}</td>
                            <td>
                                @if(!empty($item->customizations) && $item->customizations->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($item->customizations as $c)
                                            <span class="inline-block px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded-md">
                                                {{ $c->option_type ?? '' }}: {{ $c->option_name ?? '' }}
                                                @if(($c->price_snapshot ?? 0) > 0)
                                                    (+₱{{ number_format($c->price_snapshot, 2) }})
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state icon="package" title="No items" description="This order has no items." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card title="Design Files" icon="file">
            @if(isset($designFiles) && $designFiles->count() > 0)
                <div class="space-y-3">
                    @foreach($designFiles as $file)
                        <div class="border border-border rounded-lg p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-medium">{{ $file->file_name }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        Version {{ $file->version }} • {{ number_format(($file->file_size ?? 0) / 1024 / 1024, 2) }} MB • {{ date('M d, Y', strtotime($file->created_at)) }}
                                    </div>
                                    @if(!empty($file->design_notes))
                                        <div class="text-sm mt-1 text-muted-foreground">{{ $file->design_notes }}</div>
                                    @endif
                                    <div class="text-xs text-muted-foreground mt-1">
                                        Uploaded by: {{ $file->uploaded_by_name ?? 'Unknown' }}
                                        @if(!empty($file->approved_by_name))
                                            • Approved by: {{ $file->approved_by_name }}
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(($file->is_approved ?? false))
                                        <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md">Approved</span>
                                    @else
                                        <span class="inline-block px-2 py-1 text-xs bg-warning/10 text-warning rounded-md">Pending</span>
                                    @endif
                                    @if(!empty($file->file_path))
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm">Download</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted-foreground py-6">No design files uploaded yet</div>
            @endif
        </x-admin.card>

        <x-admin.card title="Status History" icon="clock">
            @if(isset($statusHistory) && $statusHistory->count() > 0)
                <div class="space-y-3">
                    @foreach($statusHistory as $h)
                        <div class="border border-border rounded-lg p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-medium">{{ $h->status_name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-muted-foreground">{{ $h->remarks ?? '' }}</div>
                                    <div class="text-xs text-muted-foreground mt-1">
                                        {{ isset($h->timestamp) ? date('M d, Y H:i', strtotime($h->timestamp)) : '' }}
                                        @if(!empty($h->user_name))
                                            • by {{ $h->user_name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted-foreground py-6">No status history yet</div>
            @endif
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Update Status" icon="edit-2">
            <form method="POST" action="{{ route('admin.orders.update-status', $order->purchase_order_id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-2">New Status</label>
                    <select name="status_id" required class="w-full px-4 py-2 border border-input rounded-md bg-background">
                        <option value="">Select status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->status_id }}">{{ $status->status_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Remarks</label>
                    <textarea name="remarks" rows="3" class="w-full px-4 py-2 border border-input rounded-md bg-background" placeholder="Optional notes..."></textarea>
                </div>

                <div>
                    <x-admin.button type="submit" variant="primary" icon="check-circle" class="w-full">Update Status</x-admin.button>
                </div>
            </form>
        </x-admin.card>

        <x-admin.card title="Actions" icon="settings">
            <div class="space-y-2">
                <x-admin.button variant="outline" icon="arrow-left" href="{{ route('admin.orders') }}" class="w-full">Back to Orders</x-admin.button>
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
