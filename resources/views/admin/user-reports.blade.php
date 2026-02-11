@extends('layouts.admin-layout')

@section('title', 'User Reports')
@section('page-title', 'User Reports')
@section('page-subtitle', 'Reports submitted by customers')

@section('content')
    <x-admin.card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Reports</h3>
                    <p class="text-sm text-muted-foreground">Review and resolve reports about businesses and services.</p>
                </div>
            </div>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/30">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Reporter</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-muted-foreground">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($reports as $r)
                        <tr class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ !empty($r->created_at) ? date('M d, Y H:i', strtotime($r->created_at)) : '' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $r->reporter_name ?? $r->reporter_id }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if(!empty($r->enterprise_id))
                                    <div class="font-medium">Enterprise</div>
                                    <div class="text-xs text-muted-foreground">{{ $r->enterprise_name ?? $r->enterprise_id }}</div>
                                @elseif(!empty($r->service_id))
                                    <div class="font-medium">Service</div>
                                    <div class="text-xs text-muted-foreground">{{ $r->service_name ?? $r->service_id }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $r->reason }}</div>
                                @if(!empty($r->description))
                                    <div class="text-xs text-muted-foreground mt-1">{{ $r->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ ($r->status ?? 'open') === 'resolved' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                    {{ ucfirst($r->status ?? 'open') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(($r->status ?? 'open') !== 'resolved')
                                    <form action="{{ route('admin.user-reports.resolve', $r->report_id) }}" method="POST" data-up-global-loader>
                                        @csrf
                                        <button type="submit" class="admin-btn admin-btn-success admin-btn-sm" data-up-button-loader>Resolve</button>
                                    </form>
                                @else
                                    <span class="text-xs text-muted-foreground">Resolved</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-sm text-muted-foreground" colspan="6">No reports.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    </x-admin.card>
@endsection
