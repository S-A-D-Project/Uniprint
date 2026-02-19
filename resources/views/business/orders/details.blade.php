@extends('layouts.business')

@section('title', 'Order Details - ' . $order->order_no)
@section('page-title', 'Order #' . $order->order_no)
@section('page-subtitle', 'Placed on ' . date('F d, Y', strtotime($order->created_at)))

@section('header-actions')
<x-ui.tooltip text="Go back to orders list">
    <a href="{{ route('business.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
        <i data-lucide="arrow-left" class="h-4 w-4"></i>
        Back to Orders
    </a>
</x-ui.tooltip>

<x-ui.tooltip text="Print this order">
    <a href="{{ route('business.orders.print', $order->purchase_order_id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth">
        <i data-lucide="printer" class="h-4 w-4"></i>
        Print
    </a>
</x-ui.tooltip>
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

            @php
                $canRequestExtension = false;
                try {
                    if (!empty($order->due_date)) {
                        $canRequestExtension = \Carbon\Carbon::parse($order->due_date)->lt(\Carbon\Carbon::today());
                    }
                } catch (\Exception $e) {
                    $canRequestExtension = false;
                }
            @endphp

            @if(\Illuminate\Support\Facades\Schema::hasTable('order_extension_requests') && $canRequestExtension)
                <div class="bg-card border border-border rounded-xl shadow-card p-6">
                    <h3 class="font-bold mb-3">Extension Request</h3>

                    @if(!empty($latestExtensionRequest))
                        <div class="text-sm text-muted-foreground mb-3">
                            Latest: <span class="font-medium text-foreground">{{ ucfirst($latestExtensionRequest->status ?? 'pending') }}</span>
                            @if(!empty($latestExtensionRequest->requested_days))
                                • {{ (int) $latestExtensionRequest->requested_days }} day(s)
                            @endif
                            @if(!empty($latestExtensionRequest->proposed_due_date))
                                • Proposed due: {{ date('M d, Y', strtotime($latestExtensionRequest->proposed_due_date)) }}
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('business.orders.extension.request', $order->purchase_order_id) }}" method="POST" class="space-y-3" data-up-global-loader>
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-2">Request extension (days)</label>
                            <input type="number" name="requested_days" min="1" max="60" value="3"
                                   class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Message (optional)</label>
                            <textarea name="message" rows="2" maxlength="500"
                                      class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth" data-up-button-loader>
                            Request Extension From Customer
                        </button>
                    </form>
                </div>
            @endif

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
                                            <form action="{{ route('business.design-files.approve', $file->file_id) }}" method="POST" class="inline" data-up-global-loader>
                                                @csrf
                                                <x-ui.tooltip text="Approve this design file">
                                                    <button type="submit" class="px-3 py-1 text-sm bg-success text-white rounded-md hover:bg-success/90" data-up-button-loader>
                                                        Approve
                                                    </button>
                                                </x-ui.tooltip>
                                            </form>
                                            <x-ui.tooltip text="Reject this design file">
                                                <button type="button" onclick="openRejectDesignFileModal('{{ $file->file_id }}')" 
                                                        class="px-3 py-1 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90">
                                                    Reject
                                                </button>
                                            </x-ui.tooltip>
                                        @endif
                                        <x-ui.tooltip text="Download this file">
                                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" 
                                               class="px-3 py-1 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/90">
                                                Download
                                            </a>
                                        </x-ui.tooltip>
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
                    @if(!empty($order->due_date))
                        @php
                            $today = \Carbon\Carbon::today()->toDateString();
                            $dueSoon = \Carbon\Carbon::today()->addDay()->toDateString();
                            $dueDate = $order->due_date;
                            $isOverdue = $dueDate < $today;
                            $isDueSoon = ! $isOverdue && $dueDate <= $dueSoon;
                        @endphp
                        <div>
                            <p class="text-sm text-muted-foreground">Due</p>
                            <div class="flex items-center gap-2">
                                <p class="font-medium">{{ date('M d, Y', strtotime($dueDate)) }}</p>
                                @if($isOverdue)
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-md bg-destructive/10 text-destructive">Overdue</span>
                                @elseif($isDueSoon)
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-md bg-warning/10 text-warning">Due soon</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-muted-foreground">Delivery Date</p>
                        <p class="font-medium">{{ date('M d, Y', strtotime($order->delivery_date)) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h3 class="font-bold mb-4">Payment</h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Method</p>
                        <p class="font-medium">{{ $order->payment_method ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        @if(!empty($isPaid))
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-md bg-success/10 text-success">Paid</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-md bg-warning/10 text-warning">Unpaid</span>
                        @endif
                    </div>

                    @if(isset($payment) && $payment)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-sm text-muted-foreground">Paid</p>
                                <p class="font-medium">₱{{ number_format((float) ($payment->amount_paid ?? 0), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Due</p>
                                <p class="font-medium">₱{{ number_format((float) ($payment->amount_due ?? 0), 2) }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($canConfirmPayment))
                    <form action="{{ route('business.orders.payment.confirm', $order->purchase_order_id) }}" method="POST" class="mt-4" data-up-global-loader>
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth" data-up-button-loader
                                onclick="return confirm('Confirm that payment has been received for this order?');">
                            Confirm Payment
                        </button>
                    </form>
                @endif
            </div>

            <!-- Linear Status Actions -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold">Next Action</h3>
                    <span class="text-sm text-muted-foreground">Current: {{ $currentStatusName ?? 'Pending' }}</span>
                </div>

                @php
                    $needsDownpayment = false;
                    $downpaymentPercent = 0;
                    $requiredDownpaymentAmount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_downpayment') && \Illuminate\Support\Facades\Schema::hasColumn('services', 'downpayment_percent')) {
                            $downpaymentPercent = (float) (\Illuminate\Support\Facades\DB::table('order_items')
                                ->join('services', 'order_items.service_id', '=', 'services.service_id')
                                ->where('order_items.purchase_order_id', $order->purchase_order_id)
                                ->where('services.requires_downpayment', true)
                                ->max('services.downpayment_percent') ?? 0);
                            $needsDownpayment = $downpaymentPercent > 0;
                            if ($needsDownpayment) {
                                $requiredDownpaymentAmount = ((float) ($order->total ?? 0)) * ($downpaymentPercent / 100);
                            }
                        }
                    } catch (\Exception $e) {
                        $needsDownpayment = false;
                        $downpaymentPercent = 0;
                        $requiredDownpaymentAmount = 0;
                    }
                    $downpaymentPaid = false;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                            $p = \Illuminate\Support\Facades\DB::table('payments')
                                ->where('purchase_order_id', $order->purchase_order_id)
                                ->first();
                            if ($p && !empty($p->is_verified) && (float) ($p->amount_paid ?? 0) + 0.00001 >= (float) ($requiredDownpaymentAmount ?? 0)) {
                                $downpaymentPaid = true;
                            }
                        } else {
                            $downpaymentPaid = (($order->payment_status ?? null) === 'paid');
                        }
                    } catch (\Exception $e) {
                        $downpaymentPaid = (($order->payment_status ?? null) === 'paid');
                    }
                @endphp

                @if($needsDownpayment && ! $downpaymentPaid && (($currentStatusName ?? null) === 'Confirmed' || ($currentStatusName ?? null) === 'Pending'))
                    <div class="mb-4 p-3 border border-amber-200 bg-amber-50 rounded-lg">
                        <p class="text-sm font-medium text-amber-800">Downpayment required</p>
                        <p class="text-sm text-amber-700 mt-1">This order requires a {{ number_format($downpaymentPercent, 2) }}% downpayment before production can start.</p>
                        <form action="{{ route('business.orders.downpayment-received', $order->purchase_order_id) }}" method="POST" class="mt-3" data-up-global-loader>
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-amber-600 text-white font-medium rounded-md hover:bg-amber-700 transition-smooth" data-up-button-loader
                                    onclick="return confirm('Mark downpayment as received? This will allow the order to start production.');">
                                Mark Downpayment Received
                            </button>
                        </form>
                    </div>
                @endif

                @if(!empty($businessActions))
                    @php
                        $cancelAction = null;
                        $advanceAction = null;
                        foreach ($businessActions as $action) {
                            if (($action['name'] ?? null) === 'Cancelled') {
                                $cancelAction = $action;
                                continue;
                            }
                            if ($advanceAction === null) {
                                $advanceAction = $action;
                            }
                        }
                    @endphp

                    <div class="space-y-3">
                        @if($advanceAction)
                            <form action="{{ route('business.orders.update-status', $order->purchase_order_id) }}" method="POST" data-up-global-loader>
                                @csrf
                                <input type="hidden" name="status_id" value="{{ $advanceAction['id'] }}">
                                <button type="submit"
                                        class="w-full px-4 py-2 font-medium rounded-md transition-smooth
                                            @if(($advanceAction['name'] ?? null) === 'Confirmed') bg-success text-white hover:opacity-90
                                            @elseif(($advanceAction['name'] ?? null) === 'In Progress') bg-primary text-primary-foreground hover:shadow-glow
                                            @elseif(in_array(($advanceAction['name'] ?? null), ['Ready for Pickup', 'Delivered'], true)) bg-amber-500 text-white hover:opacity-90
                                            @else bg-primary text-primary-foreground hover:shadow-glow @endif" data-up-button-loader>
                                    {{ $advanceAction['label'] ?? $advanceAction['name'] }}
                                </button>
                            </form>
                        @endif

                        @if($cancelAction)
                            <button type="button"
                                    class="w-full px-4 py-2 bg-destructive text-destructive-foreground font-medium rounded-md hover:bg-destructive/90 transition-smooth"
                                    onclick="openOrderStatusActionModal('{{ $order->purchase_order_id }}', '{{ $cancelAction['id'] }}', @json($cancelAction['label'] ?? $cancelAction['name']))">
                                {{ $cancelAction['label'] ?? $cancelAction['name'] }}
                            </button>
                        @endif
                    </div>
                @else
                    @if(($currentStatusName ?? null) === 'Pending')
                        <p class="text-sm text-muted-foreground">Waiting for business confirmation.</p>
                    @elseif(in_array(($currentStatusName ?? null), ['Ready for Pickup', 'Delivered'], true))
                        <p class="text-sm text-muted-foreground">Waiting for customer pickup/confirmation.</p>
                    @else
                        <p class="text-sm text-muted-foreground">No actions available for the current status.</p>
                    @endif
                @endif

                @if(($currentStatusName ?? null) === 'Pending' && empty($cancelAction))
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
