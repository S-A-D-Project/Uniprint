@extends('layouts.app')

@section('title', 'Order Details')

@section('dashboard-route', route('customer.dashboard'))

@section('sidebar')
    <a href="{{ route('customer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('customer.enterprises') }}" class="nav-link">
        <i class="bi bi-shop"></i>Browse Shops
    </a>
    <a href="{{ route('customer.orders') }}" class="nav-link active">
        <i class="bi bi-bag"></i>My Orders
    </a>
    <a href="{{ route('customer.design-assets') }}" class="nav-link">
        <i class="bi bi-images"></i>My Designs
    </a>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0"><i class="bi bi-receipt me-2"></i>Order #{{ $order->purchase_order_id }}</h2>
        <p class="text-muted mb-0">Order details and tracking</p>
    </div>
    <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Order Status -->
        <div class="card mb-4">
            <div class="card-body text-center">
                @if($order->current_status == 'Pending')
                    <i class="bi bi-clock text-warning" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Pending</h3>
                    <p class="text-muted">Your order is being reviewed by the shop</p>
                @elseif($order->current_status == 'In Progress')
                    <i class="bi bi-arrow-repeat text-info" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order In Progress</h3>
                    <p class="text-muted">Your order is being prepared</p>
                @elseif($order->current_status == 'Shipped')
                    <i class="bi bi-truck text-primary" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Shipped</h3>
                    <p class="text-muted">Your order is on its way!</p>
                @else
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Complete</h3>
                    <p class="text-muted">Your order has been delivered</p>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Order Items</h5>
            </div>
            <div class="card-body">
                @foreach($order->orderItems as $item)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">{{ $item->service->service_name }}</h6>
                            <span class="badge bg-secondary">Quantity: {{ $item->quantity }}</span>
                        </div>
                        <h5 class="text-primary mb-0">₱{{ number_format($item->item_subtotal, 2) }}</h5>
                    </div>

                    @if($item->customizations->count() > 0)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2"><strong>Customizations:</strong></small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($item->customizations as $custom)
                            <span class="badge bg-light text-dark">
                                {{ $custom->option->customizationGroup->group_name }}: {{ $custom->option->option_name }}
                                @if($custom->option_price_snapshot > 0)
                                    (+₱{{ number_format($custom->option_price_snapshot, 2) }})
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($item->notes_to_enterprise)
                    <div class="mt-3 alert alert-info mb-0">
                        <strong><i class="bi bi-chat-left-text me-1"></i>Your Notes:</strong> {{ $item->notes_to_enterprise }}
                    </div>
                    @endif
                </div>
                @endforeach

                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Total Amount:</h5>
                        <h4 class="text-primary mb-0">₱{{ number_format($order->total_order_amount, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Order Timeline</h5>
            </div>
            <div class="card-body">
                @foreach($order->statusHistory as $history)
                <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                    <div class="me-3">
                        @if($history->status_name == 'Complete')
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                        @elseif($history->status_name == 'Shipped')
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-truck"></i>
                            </div>
                        @elseif($history->status_name == 'In Progress')
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                        @else
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-clock"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $history->status_name }}</h6>
                        <small class="text-muted">
                            {{ $history->status_timestamp->format('M d, Y H:i') }}
                        </small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Shop Information -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Shop Details</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2">{{ $order->enterprise->enterprise_name }}</h6>
                <span class="badge bg-info mb-3">{{ $order->enterprise->category }}</span>

                @if($order->enterprise->address_text)
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i>{{ $order->enterprise->address_text }}
                </p>
                @endif

                @if($order->enterprise->contact_email)
                <p class="text-muted small mb-0">
                    <i class="bi bi-envelope me-1"></i>{{ $order->enterprise->contact_email }}
                </p>
                @endif
            </div>
        </div>

        <!-- Order Information -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Order Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Order ID</small>
                    <strong>#{{ $order->purchase_order_id }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Order Date</small>
                    <strong>{{ isset($order->order_creation_date) ? (is_string($order->order_creation_date) ? date('M d, Y H:i', strtotime($order->order_creation_date)) : $order->order_creation_date->format('M d, Y H:i')) : 'N/A' }}</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Current Status</small>
                    @if($order->current_status == 'Pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($order->current_status == 'In Progress')
                        <span class="badge bg-info">In Progress</span>
                    @elseif($order->current_status == 'Shipped')
                        <span class="badge bg-primary">Shipped</span>
                    @else
                        <span class="badge bg-success">Complete</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if($order->transactions->count() > 0)
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment</h5>
            </div>
            <div class="card-body">
                @foreach($order->transactions as $transaction)
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Method:</small>
                        <strong>{{ $transaction->payment_method }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Amount:</small>
                        <strong>₱{{ number_format($transaction->amount_paid, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Status:</small>
                        @if($transaction->is_verified)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
