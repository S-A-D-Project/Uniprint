@extends('layouts.app')

@section('title', 'Order Details')

@section('dashboard-route', route('business.dashboard'))

@section('sidebar')
    <a href="{{ route('business.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('business.orders') }}" class="nav-link active">
        <i class="bi bi-cart"></i>Orders
    </a>
    <a href="{{ route('business.products') }}" class="nav-link">
        <i class="bi bi-box-seam"></i>Products
    </a>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0"><i class="bi bi-cart me-2"></i>Order #{{ $order->order_id }}</h2>
        <p class="text-muted mb-0">Order details and management</p>
    </div>
    <a href="{{ route('business.orders') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
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
                            <h6 class="mb-1">{{ $item->product->product_name }}</h6>
                            <span class="badge bg-secondary">Quantity: {{ $item->quantity }}</span>
                        </div>
                        <h5 class="text-primary mb-0">₱{{ number_format($item->item_subtotal, 2) }}</h5>
                    </div>

                    @if($item->customizations->count() > 0)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2"><strong>Customizations:</strong></small>
                        @foreach($item->customizations as $custom)
                        <span class="badge bg-light text-dark me-2">
                            {{ $custom->option->customizationGroup->group_name }}: {{ $custom->option->option_name }}
                            @if($custom->option_price_snapshot > 0)
                                (+₱{{ number_format($custom->option_price_snapshot, 2) }})
                            @endif
                        </span>
                        @endforeach
                    </div>
                    @endif

                    @if($item->notes_to_enterprise)
                    <div class="mt-3 alert alert-info mb-0">
                        <strong>Customer Notes:</strong> {{ $item->notes_to_enterprise }}
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

        <!-- Status History -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($order->statusHistory as $history)
                    <div class="d-flex mb-3">
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
                                @if($history->staff)
                                    - by {{ $history->staff->staff_name }}
                                @endif
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2">{{ $order->customer->username ?? $order->customer->name ?? 'Customer' }}</h6>
                @if(!empty($order->customer->email ?? null))
                <p class="text-muted mb-0">
                    <i class="bi bi-envelope me-2"></i>{{ $order->customer->email }}
                </p>
                @endif
            </div>
        </div>

        <!-- Order Status Update -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Update Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('business.order.update-status', $order->order_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="status" class="form-label">Current Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Pending" {{ $order->current_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="In Progress" {{ $order->current_status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Shipped" {{ $order->current_status == 'Shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="Complete" {{ $order->current_status == 'Complete' ? 'selected' : '' }}>Complete</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment</h5>
            </div>
            <div class="card-body">
                @if($order->transactions->count() > 0)
                    @foreach($order->transactions as $transaction)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Method:</small>
                            <strong>{{ $transaction->payment_method }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Amount:</small>
                            <strong>₱{{ number_format($transaction->amount_paid, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Date:</small>
                            <small>{{ $transaction->payment_date_time->format('M d, Y') }}</small>
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
                @else
                    <p class="text-muted mb-0">No payment information available</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
