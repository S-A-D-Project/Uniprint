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
                                            <button onclick="openRejectModal('{{ $file->file_id }}')" 
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

            <!-- Order Actions -->
            @if(($currentStatusName ?? null) === 'Pending')
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h3 class="font-bold mb-4">Order Actions</h3>
                <form action="{{ route('business.orders.confirm', $order->purchase_order_id) }}" method="POST" class="js-confirm-order">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-success text-white font-medium rounded-md hover:opacity-90 transition-smooth">
                        Confirm Order
                    </button>
                </form>
            </div>
            @endif

            <!-- Update Status -->
            <div class="bg-card border border-border rounded-xl shadow-card p-6">
                <h3 class="font-bold mb-4">Update Order Status</h3>
                <form action="{{ route('business.orders.update-status', $order->purchase_order_id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">New Status</label>
                            <select name="status_id" required
                                    class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                                <option value="">Select status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->status_id }}">{{ $status->status_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Remarks (Optional)</label>
                            <textarea name="remarks" rows="3" 
                                      class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
</script>
@endpush

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-card rounded-xl shadow-card-hover max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Reject Design File</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Rejection Reason</label>
                    <textarea name="rejection_reason" required rows="4" 
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="flex-1 px-4 py-2 border border-input rounded-md hover:bg-secondary">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90">
                        Reject File
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(fileId) {
    const form = document.getElementById('rejectForm');
    form.action = `/business/design-files/${fileId}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
