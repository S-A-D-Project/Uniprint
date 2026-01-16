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
    <div class="d-flex gap-2">
        @if(($currentStatusName ?? null) === 'Pending')
            <form action="{{ route('customer.orders.cancel', $order->purchase_order_id) }}" method="POST" onsubmit="return confirm('Cancel this order?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle me-2"></i>Cancel Order
                </button>
            </form>
        @endif
        @if(in_array($currentStatusName ?? null, ['Ready for Pickup', 'Delivered'], true))
            <form action="{{ route('customer.orders.confirm-completion', $order->purchase_order_id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-2"></i>Confirm Completion
                </button>
            </form>
        @endif
        <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Order Status -->
        <div class="card mb-4">
            <div class="card-body text-center">
                @php
                    $status = $currentStatusName ?? ($order->status_name ?? 'Pending');
                @endphp
                @if($status === 'Pending')
                    <i class="bi bi-clock text-warning" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Pending</h3>
                    <p class="text-muted">Your order is being reviewed by the shop</p>
                @elseif($status === 'Confirmed')
                    <i class="bi bi-patch-check text-primary" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Confirmed</h3>
                    <p class="text-muted">Your order has been accepted and will be prepared soon</p>
                @elseif($status === 'In Progress')
                    <i class="bi bi-arrow-repeat text-info" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order In Progress</h3>
                    <p class="text-muted">Your order is being prepared</p>
                @elseif($status === 'Ready for Pickup')
                    <i class="bi bi-bag-check text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Ready for Pickup</h3>
                    <p class="text-muted">Your order is ready. Please pick it up when convenient</p>
                @elseif($status === 'Shipped')
                    <i class="bi bi-truck text-primary" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Shipped</h3>
                    <p class="text-muted">Your order is on its way!</p>
                @elseif($status === 'Delivered')
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Delivered</h3>
                    <p class="text-muted">Your order has been delivered</p>
                @elseif($status === 'Completed')
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Completed</h3>
                    <p class="text-muted">Thanks! You confirmed completion for this order</p>
                @elseif($status === 'Cancelled')
                    <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Order Cancelled</h3>
                    <p class="text-muted">This order has been cancelled</p>
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
                @foreach($orderItems as $item)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">{{ $item->service_name ?? 'Service' }}</h6>
                            <span class="badge bg-secondary">Quantity: {{ $item->quantity }}</span>
                        </div>
                        <h5 class="text-primary mb-0">₱{{ number_format($item->total_cost ?? ($item->quantity * ($item->unit_price ?? 0)), 2) }}</h5>
                    </div>

                    @if(isset($item->customizations) && $item->customizations->count() > 0)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2"><strong>Customizations:</strong></small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($item->customizations as $custom)
                            <span class="badge bg-light text-dark">
                                {{ $custom->option_type ?? 'Option' }}: {{ $custom->option_name ?? '' }}
                                @if(($custom->price_snapshot ?? 0) > 0)
                                    (+₱{{ number_format($custom->price_snapshot, 2) }})
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($item->custom_field_values) && $item->custom_field_values->isNotEmpty())
                    <div class="mt-3">
                        <small class="text-muted d-block mb-2"><strong>Additional Information:</strong></small>
                        <div class="d-flex flex-column gap-1">
                            @foreach($item->custom_field_values as $field)
                                <div class="small">
                                    <strong>{{ $field->label }}:</strong>
                                    <span>{{ $field->value }}</span>
                                </div>
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
                        <h4 class="text-primary mb-0">₱{{ number_format($order->total_order_amount ?? ($order->total ?? 0), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Design Files -->
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Design Files</h5>
                @if(!empty($requiresFileUpload))
                    <span class="badge bg-danger">Required</span>
                @else
                    <span class="badge bg-secondary">Optional</span>
                @endif
            </div>
            <div class="card-body">
                @if(!empty($requiresFileUpload) && (!isset($designFiles) || $designFiles->count() === 0))
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Action needed:</strong>
                        This service requires a design file upload before the shop can proceed.
                    </div>
                @endif

                @if(isset($designFiles) && $designFiles->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($designFiles as $file)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $file->file_name }}</div>
                                        <div class="small text-muted">
                                            Version {{ $file->version }} • {{ number_format(($file->file_size ?? 0) / 1024 / 1024, 2) }} MB • {{ date('M d, Y', strtotime($file->created_at)) }}
                                        </div>
                                        @if(!empty($file->design_notes))
                                            <div class="small text-muted">Notes: {{ $file->design_notes }}</div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ asset('storage/' . $file->file_path) }}">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                        @if(empty($file->is_approved))
                                            <form action="{{ route('customer.orders.delete-design', [$order->purchase_order_id, $file->file_id]) }}" method="POST" onsubmit="return confirm('Delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-3">No design files uploaded yet.</p>
                @endif

                <form action="{{ route('customer.orders.upload-design', $order->purchase_order_id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload a design file</label>
                        <input type="file" name="design_file" class="form-control" required>
                        <div class="form-text">Accepted: JPG, PNG, PDF, AI, PSD, EPS. Max 50MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (optional)</label>
                        <textarea name="design_notes" class="form-control" rows="2" placeholder="Any notes for the shop about this file..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Order Timeline</h5>
            </div>
            <div class="card-body">
                @foreach($statusHistory as $history)
                <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                    <div class="me-3">
                        @if($history->status_name == 'Completed')
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                        @elseif($history->status_name == 'Delivered' || $history->status_name == 'Shipped')
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-truck"></i>
                            </div>
                        @elseif($history->status_name == 'In Progress')
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                        @elseif($history->status_name == 'Cancelled')
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-x-lg"></i>
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
                            {{ isset($history->timestamp) ? (is_string($history->timestamp) ? date('M d, Y H:i', strtotime($history->timestamp)) : $history->timestamp->format('M d, Y H:i')) : 'N/A' }}
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
                <h6 class="mb-2">{{ $order->enterprise_name ?? 'Shop' }}</h6>

                @if(!empty($order->address))
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i>{{ $order->address }}
                </p>
                @endif

                @if(!empty($order->contact_number))
                <p class="text-muted small mb-0">
                    <i class="bi bi-telephone me-1"></i>{{ $order->contact_number }}
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
                    @php
                        $status = $currentStatusName ?? 'Pending';
                    @endphp
                    @if($status === 'Pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($status === 'Confirmed')
                        <span class="badge bg-primary">Confirmed</span>
                    @elseif($status === 'In Progress')
                        <span class="badge bg-info">In Progress</span>
                    @elseif($status === 'Ready for Pickup')
                        <span class="badge bg-success">Ready for Pickup</span>
                    @elseif($status === 'Delivered')
                        <span class="badge bg-success">Delivered</span>
                    @elseif($status === 'Completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($status === 'Cancelled')
                        <span class="badge bg-danger">Cancelled</span>
                    @else
                        <span class="badge bg-secondary">{{ $status }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if(!empty($transaction))
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Method:</small>
                        <strong>{{ $transaction->payment_method }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Amount:</small>
                        <strong>₱{{ number_format($transaction->amount_paid ?? 0, 2) }}</strong>
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
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
