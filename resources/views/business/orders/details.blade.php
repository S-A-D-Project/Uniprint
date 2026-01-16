@extends('layouts.business')

@section('title', 'Order Details - ' . $order->order_no)
@section('page-title', 'Order #' . $order->order_no)
@section('page-subtitle', 'Placed on ' . date('F d, Y', strtotime($order->created_at)))

@section('header-actions')
<a href="{{ route('business.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Orders
</a>
@endsection

@section('content')

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h2 class="text-xl font-bold mb-4">Order Items</h2>
                <div class="space-y-4">
                    @foreach($orderItems as $item)
                        <div class="border border-border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold">{{ $item->service_name }}</h3>
                                    <p class="text-sm text-muted-foreground">Quantity: {{ $item->quantity }}</p>
                                </div>
                                <p class="font-bold">₱{{ number_format($item->total_cost, 2) }}</p>
                            </div>
                            
                            @if($item->customizations->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-border">
                                    <p class="text-sm font-medium mb-1">Customizations:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item->customizations as $custom)
                                            <span class="inline-block px-2 py-1 text-xs bg-primary/10 text-primary rounded-md">
                                                {{ $custom->option_name }}: {{ $custom->option_type }}
                                                @if($custom->price_snapshot > 0)
                                                    (+₱{{ number_format($custom->price_snapshot, 2) }})
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($item->custom_field_values) && $item->custom_field_values->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-border">
                                    <p class="text-sm font-medium mb-1">Additional Information:</p>
                                    <div class="space-y-1 text-sm text-muted-foreground">
                                        @foreach($item->custom_field_values as $field)
                                            <div class="flex gap-2">
                                                <span class="font-medium text-foreground">{{ $field->label }}:</span>
                                                <span class="break-words">{{ $field->value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t border-border space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Subtotal</span>
                        <span>₱{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Shipping Fee</span>
                        <span>₱{{ number_format($order->shipping_fee, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm text-success">
                            <span>Discount</span>
                            <span>-₱{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-border">
                        <span>Total</span>
                        <span class="text-primary">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Design Files -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h2 class="text-xl font-bold mb-4">Design Files</h2>
                @if($designFiles->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($designFiles as $file)
                            <div class="border border-border rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3">
                                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                            <i data-lucide="file" class="h-6 w-6 text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ $file->file_name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                Version {{ $file->version }} • 
                                                {{ number_format($file->file_size / 1024 / 1024, 2) }} MB •
                                                {{ date('M d, Y', strtotime($file->created_at)) }}
                                            </p>
                                            @if($file->design_notes)
                                                <p class="text-sm mt-1 text-muted-foreground">{{ $file->design_notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        @if($file->is_approved)
                                            <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md">
                                                Approved
                                            </span>
                                        @else
                                            <form action="{{ route('business.design-files.approve', $file->file_id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 text-sm bg-success text-white rounded-md hover:bg-success/90">
                                                    Approve
                                                </button>
                                            </form>
                                            <button type="button" onclick="openRejectDesignFileModal('{{ $file->file_id }}')" 
                                                    class="px-3 py-1 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90">
                                                Reject
                                            </button>
                                        @endif
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" 
                                           class="px-3 py-1 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/90">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted-foreground py-8">No design files uploaded yet</p>
                @endif
            </div>

            <!-- Status History -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h2 class="text-xl font-bold mb-4">Status History</h2>
                <div class="space-y-4">
                    @foreach($statusHistory as $history)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="check" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">{{ $history->status_name }}</p>
                                        <p class="text-sm text-muted-foreground">by {{ $history->user_name }}</p>
                                    </div>
                                    <span class="text-sm text-muted-foreground">
                                        {{ date('M d, Y g:i A', strtotime($history->timestamp)) }}
                                    </span>
                                </div>
                                @if($history->remarks)
                                    <p class="text-sm text-muted-foreground mt-1">{{ $history->remarks }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h3 class="font-bold mb-4">Customer Information</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="font-medium">{{ $order->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Email</p>
                        <p class="font-medium">{{ $order->customer_email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Delivery Date</p>
                        <p class="font-medium">{{ date('M d, Y', strtotime($order->delivery_date)) }}</p>
                    </div>
                </div>
            </div>

            <!-- Linear Status Actions -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold">Next Action</h3>
                    <span class="text-sm text-muted-foreground">Current: {{ $currentStatusName ?? 'Pending' }}</span>
                </div>

                @if(!empty($businessActions))
                    <div class="space-y-3">
                        @foreach($businessActions as $action)
                            <button type="button"
                                    class="w-full px-4 py-2 font-medium rounded-md transition-smooth
                                        @if($action['name'] === 'Cancelled') bg-destructive text-destructive-foreground hover:bg-destructive/90
                                        @elseif($action['name'] === 'Confirmed') bg-success text-white hover:opacity-90
                                        @elseif($action['name'] === 'In Progress') bg-primary text-primary-foreground hover:shadow-glow
                                        @elseif($action['name'] === 'Ready for Pickup' || $action['name'] === 'Delivered') bg-amber-500 text-white hover:opacity-90
                                        @else bg-primary text-primary-foreground hover:shadow-glow @endif"
                                    onclick="openOrderStatusActionModal('{{ $order->purchase_order_id }}', '{{ $action['id'] }}', @json($action['name']))">
                                {{ $action['name'] }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-muted-foreground">Waiting for customer confirmation.</p>
                @endif

                @if(($currentStatusName ?? null) === 'Pending')
                    <div class="mt-4 border-t border-border pt-4">
                        <p class="text-sm font-medium mb-2">Need to undo?</p>
                        <button type="button"
                                class="w-full px-4 py-2 bg-destructive text-destructive-foreground font-medium rounded-md hover:bg-destructive/90 transition-smooth"
                                onclick="openOrderStatusActionModal('{{ $order->purchase_order_id }}', '{{ $statusIds['Cancelled'] ?? '' }}', 'Cancelled')">
                            Cancel Order
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<x-modals.reject-design-file />
<x-modals.order-status-action />

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form.js-confirm-order');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const ok = window.UniPrintUI?.confirm
                ? await window.UniPrintUI.confirm('Confirm this order? This will move it out of Pending.', { title: 'Confirm Order', confirmText: 'Confirm', cancelText: 'Cancel' })
                : confirm('Confirm this order?');
            if (ok) form.submit();
        });
    });

    function openRejectDesignFileModal(fileId) {
        const modalEl = document.getElementById('rejectDesignFileModal');
        const formEl = document.getElementById('rejectDesignFileForm');
        if (!modalEl || !formEl) return;

        formEl.action = `{{ route('business.design-files.reject', ':fileId') }}`.replace(':fileId', fileId);

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    window.openRejectDesignFileModal = openRejectDesignFileModal;

    function openOrderStatusActionModal(orderId, statusId, statusName) {
        const modalEl = document.getElementById('orderStatusActionModal');
        const formEl = document.getElementById('orderStatusActionForm');
        const statusIdEl = document.getElementById('orderStatusActionStatusId');
        const statusNameEl = document.getElementById('orderStatusActionStatusName');
        const remarksEl = document.getElementById('orderStatusActionRemarks');
        const submitEl = document.getElementById('orderStatusActionSubmit');

        if (!modalEl || !formEl || !statusIdEl || !statusNameEl || !remarksEl || !submitEl) return;

        formEl.action = `{{ route('business.orders.update-status', ':orderId') }}`.replace(':orderId', orderId);
        statusIdEl.value = statusId;
        statusNameEl.textContent = statusName || 'Update';
        remarksEl.value = '';
        submitEl.textContent = statusName || 'Update';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    window.openOrderStatusActionModal = openOrderStatusActionModal;
</script>
@endpush
@endsection
