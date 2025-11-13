@extends('layouts.app')

@section('title', 'Orders')

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
<div class="page-header">
    <h2 class="mb-0"><i class="bi bi-cart me-2"></i>Orders</h2>
    <p class="text-muted mb-0">Manage all orders for {{ $enterprise->enterprise_name }}</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->order_id }}</strong></td>
                        <td>
                            <i class="bi bi-person-circle me-1"></i>
                            {{ $order->customer->username ?? $order->customer->name ?? 'Customer' }}
                            @if(!empty($order->customer->email ?? null))
                                <br><small class="text-muted">{{ $order->customer->email }}</small>
                            @endif
                        </td>
                        <td>
                            @foreach($order->orderItems as $item)
                                <small class="d-block">{{ $item->quantity }}x {{ $item->product->product_name }}</small>
                            @endforeach
                        </td>
                        <td><strong>₱{{ number_format($order->total_order_amount, 2) }}</strong></td>
                        <td>
                            @if($order->current_status == 'Pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @elseif($order->current_status == 'In Progress')
                                <span class="badge bg-info">
                                    <i class="bi bi-arrow-repeat"></i> In Progress
                                </span>
                            @elseif($order->current_status == 'Shipped')
                                <span class="badge bg-primary">
                                    <i class="bi bi-truck"></i> Shipped
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Complete
                                </span>
                            @endif
                        </td>
                        <td>{{ isset($order->order_creation_date) ? (is_string($order->order_creation_date) ? date('M d, Y H:i', strtotime($order->order_creation_date)) : $order->order_creation_date->format('M d, Y H:i')) : 'N/A' }}</td>
                        <td>
                            <a href="{{ route('business.order.details', $order->order_id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-cart-x"></i>
                                <h5>No orders found</h5>
                                <p class="text-muted">Orders will appear here once customers place them</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer bg-white">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
