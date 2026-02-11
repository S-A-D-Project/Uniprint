<div class="space-y-6">
    <!-- Summary Header -->
    <div class="bg-secondary/30 p-4 rounded-lg flex flex-wrap justify-between items-center gap-4">
        <div>
            <p class="text-sm text-muted-foreground">Order Number</p>
            <p class="text-lg font-bold">#{{ $order->order_no }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-muted-foreground">Status</p>
            <x-admin.badge variant="{{ $order->status_name == 'Pending' ? 'warning' : 'primary' }}">
                {{ $order->status_name ?? 'Unknown' }}
            </x-admin.badge>
        </div>
    </div>

    <!-- Order Items -->
    <div>
        <h3 class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="package" class="h-4 w-4"></i>
            Order Items
        </h3>
        <div class="space-y-3">
            @foreach($orderItems as $item)
                <div class="border border-border rounded-lg p-4 bg-card">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-foreground">{{ $item->service_name }}</p>
                            <p class="text-sm text-muted-foreground">Qty: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-bold text-foreground">₱{{ number_format($item->total_cost, 2) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Two Column Info -->
    <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-border">
        <div class="space-y-4">
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Customer</h4>
                <p class="text-sm font-medium">{{ $order->customer_name }}</p>
                <p class="text-xs text-muted-foreground">{{ $order->customer_email }}</p>
            </div>
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Enterprise</h4>
                <p class="text-sm font-medium">{{ $order->enterprise_name }}</p>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Subtotal</span>
                <span>₱{{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Shipping</span>
                <span>₱{{ number_format($order->shipping_fee ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t border-border pt-2">
                <span>Total</span>
                <span class="text-primary">₱{{ number_format($order->total ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-6 border-t border-border">
        <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth text-sm font-medium" data-bs-dismiss="modal">
            Close
        </button>
        <a href="{{ route('admin.orders.details', $order->purchase_order_id) }}" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth text-sm">
            View Full Details
        </a>
    </div>
</div>
