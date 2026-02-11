@php
    $isPaid = false;
    if (isset($payment) && $payment) {
        $amountPaid = (float) ($payment->amount_paid ?? 0);
        $amountDue = (float) ($payment->amount_due ?? 0);
        $isPaid = !empty($payment->is_verified) && ($amountPaid + 0.00001 >= $amountDue);
    } elseif (Schema::hasColumn('customer_orders', 'payment_status')) {
        $isPaid = (($order->payment_status ?? null) === 'paid');
    }
@endphp

<div class="space-y-6">
    <!-- Order Summary -->
    <div class="bg-secondary/30 p-4 rounded-lg flex justify-between items-center">
        <div>
            <p class="text-sm text-muted-foreground">Order Number</p>
            <p class="text-lg font-bold">#{{ $order->order_no }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-muted-foreground">Status</p>
            <span class="inline-block px-2 py-1 text-xs font-medium rounded-md bg-primary/10 text-primary uppercase">
                {{ $order->status_name ?? 'N/A' }}
            </span>
        </div>
    </div>

    <!-- Items -->
    <div>
        <h3 class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="package" class="h-4 w-4"></i>
            Order Items
        </h3>
        <div class="space-y-3">
            @foreach($items as $item)
                <div class="border border-border rounded-lg p-4 bg-card">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-foreground">{{ $item->service_name }}</p>
                            <p class="text-sm text-muted-foreground">Qty: {{ $item->quantity }} @ ₱{{ number_format($item->unit_price, 2) }}</p>
                        </div>
                        <p class="font-bold text-foreground">₱{{ number_format($item->total_cost, 2) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Totals & Customer -->
    <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-border">
        <div class="space-y-3">
            <h3 class="font-bold flex items-center gap-2 text-sm text-muted-foreground uppercase tracking-wider">
                <i data-lucide="user" class="h-4 w-4"></i>
                Customer Info
            </h3>
            <div>
                <p class="text-sm font-medium">{{ $order->customer_name }}</p>
                <p class="text-xs text-muted-foreground">{{ $order->customer_email }}</p>
            </div>
            @if(!empty($order->purpose))
                <div>
                    <p class="text-xs text-muted-foreground uppercase">Purpose</p>
                    <p class="text-sm">{{ $order->purpose }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Subtotal</span>
                <span>₱{{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t border-border pt-2">
                <span>Total</span>
                <span class="text-primary">₱{{ number_format($order->total ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm items-center pt-2">
                <span class="text-muted-foreground">Payment Status</span>
                @if($isPaid)
                    <span class="text-success flex items-center gap-1 font-medium">
                        <i data-lucide="check-circle" class="h-4 w-4"></i> Paid
                    </span>
                @else
                    <span class="text-warning flex items-center gap-1 font-medium">
                        <i data-lucide="clock" class="h-4 w-4"></i> Unpaid
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-6 border-t border-border">
        <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth text-sm font-medium" data-bs-dismiss="modal">
            Close
        </button>
        <a href="{{ route('business.orders.details', $order->purchase_order_id) }}" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth text-sm">
            View Full Details
        </a>
    </div>
</div>
