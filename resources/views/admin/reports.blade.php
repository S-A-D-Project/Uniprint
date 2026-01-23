@extends('layouts.admin-layout')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Business intelligence and insights')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Reports', 'url' => '#'],
];
@endphp

@section('header-actions')
    <x-admin.button variant="outline" icon="calendar" size="sm">
        Date Range
    </x-admin.button>
    <x-admin.button variant="primary" icon="download" size="sm">
        Export Report
    </x-admin.button>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Orders by Status -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <div class="w-6 h-6 bg-primary/10 rounded-lg flex items-center justify-center">
                <i data-lucide="bar-chart-3" class="h-4 w-4 text-primary"></i>
            </div>
            Orders by Status
        </h2>
        @if($orders_by_status->count() > 0)
            <div class="grid grid-cols-2 gap-4">
                @foreach($orders_by_status as $status)
                <div class="border border-border rounded-lg p-4 text-center hover:shadow-card-hover transition-smooth">
                    <h3 class="text-2xl font-bold mb-2">{{ $status->count }}</h3>
                    <span class="inline-block px-2 py-1 text-xs rounded-md font-medium
                        @if($status->status_name == 'Pending') bg-warning/10 text-warning
                        @elseif($status->status_name == 'In Progress') bg-blue-500/10 text-blue-500
                        @elseif($status->status_name == 'Shipped') bg-success/10 text-success
                        @else bg-success/10 text-success
                        @endif
                    ">{{ $status->status_name }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="bar-chart-3" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <p class="text-muted-foreground">No order data available</p>
            </div>
        @endif
    </div>

    <!-- Top Enterprises -->
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <div class="w-6 h-6 bg-primary/10 rounded-lg flex items-center justify-center">
                <i data-lucide="building-2" class="h-4 w-4 text-primary"></i>
            </div>
            Top Enterprises
        </h2>
        @if($top_enterprises->count() > 0)
            <div class="space-y-3">
                @foreach($top_enterprises as $enterprise)
                <div class="flex items-center justify-between p-3 border border-border rounded-lg hover:bg-secondary/30 transition-smooth">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i data-lucide="building" class="h-5 w-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="font-medium">{{ $enterprise->name }}</h6>
                            <p class="text-sm text-muted-foreground">Business Enterprise</p>
                        </div>
                    </div>
                    <span class="inline-block px-3 py-1 text-sm bg-primary/10 text-primary rounded-lg font-medium">
                        {{ $enterprise->orders_count }} orders
                    </span>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-muted/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="building-2" class="h-8 w-8 text-muted-foreground"></i>
                </div>
                <p class="text-muted-foreground">No enterprise data available</p>
            </div>
        @endif
    </div>
</div>

<!-- Revenue by Month -->
<x-admin.card title="Revenue by Month" icon="peso-sign" :noPadding="true">
    @if($revenue_by_month->count() > 0)
        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenue_by_month as $data)
                    <tr>
                        <td class="font-medium">{{ \Carbon\Carbon::parse($data->month)->format('F Y') }}</td>
                        <td>
                            <span class="text-lg font-bold text-success">₱{{ number_format($data->revenue, 2) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-admin.empty-state 
            icon="peso-sign"
            title="No revenue data"
            description="Revenue data will appear here once transactions are made" />
    @endif
</x-admin.card>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
