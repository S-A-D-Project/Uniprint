@extends('layouts.business')

@section('title', 'Print Order - ' . $order->order_no)
@section('page-title', 'Print Order')
@section('page-subtitle', 'Order #' . $order->order_no)

@section('header-actions')
    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:shadow-glow transition-smooth">
        <i data-lucide="printer" class="h-4 w-4"></i>
        Print
    </button>
@endsection

@section('content')
    <div class="bg-white text-black rounded-xl border border-border p-6 print:border-0 print:shadow-none print:p-0">
        <div class="flex items-start justify-between gap-6 mb-6">
            <div>
                <h2 class="text-2xl font-bold">{{ $enterprise->name ?? 'UniPrint' }}</h2>
                @if(!empty($enterprise->address))
                    <p class="text-sm text-gray-700">{{ $enterprise->address }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm"><span class="font-semibold">Order #:</span> {{ $order->order_no }}</p>
                <p class="text-sm"><span class="font-semibold">Status:</span> {{ $currentStatusName ?? 'Pending' }}</p>
                <p class="text-sm"><span class="font-semibold">Placed:</span> {{ date('M d, Y', strtotime($order->created_at)) }}</p>
                @if(!empty($order->due_date))
                    <p class="text-sm"><span class="font-semibold">Due:</span> {{ date('M d, Y', strtotime($order->due_date)) }}</p>
                @endif
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold mb-2">Customer</h3>
                <p class="text-sm"><span class="font-semibold">Name:</span> {{ $order->customer_name }}</p>
                <p class="text-sm"><span class="font-semibold">Email:</span> {{ $order->customer_email }}</p>
            </div>

            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold mb-2">Order Details</h3>
                <p class="text-sm"><span class="font-semibold">Purpose:</span> {{ $order->purpose }}</p>
                <p class="text-sm"><span class="font-semibold">Delivery Date:</span> {{ date('M d, Y', strtotime($order->delivery_date)) }}</p>
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden mb-6">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold uppercase px-4 py-3">Item</th>
                        <th class="text-left text-xs font-semibold uppercase px-4 py-3">Qty</th>
                        <th class="text-right text-xs font-semibold uppercase px-4 py-3">Unit</th>
                        <th class="text-right text-xs font-semibold uppercase px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($orderItems as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $item->service_name ?? $item->item_description }}</p>
                                @if(!empty($item->item_description) && ($item->service_name ?? null) !== $item->item_description)
                                    <p class="text-sm text-gray-600">{{ $item->item_description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₱{{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <div class="w-full max-w-sm space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span>₱{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Shipping Fee</span>
                    <span>₱{{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                @if(($order->discount ?? 0) > 0)
                    <div class="flex justify-between text-sm">
                        <span>Discount</span>
                        <span>-₱{{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-200">
                    <span>Total</span>
                    <span>₱{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <style>
            @media print {
                header, #sidebar, #sidebar-overlay, .nav-link, .animate-slide-up { display: none !important; }
                main { padding: 0 !important; }
                body { background: #fff !important; }
                .print\\:border-0 { border: 0 !important; }
                .print\\:shadow-none { box-shadow: none !important; }
                .print\\:p-0 { padding: 0 !important; }
            }
        </style>
    </div>
@endsection
