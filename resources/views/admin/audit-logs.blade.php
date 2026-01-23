@extends('layouts.admin-layout')

@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Track key actions and system changes')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Audit Logs', 'url' => '#'],
];
@endphp

@section('content')
<x-admin.card title="Audit Logs" icon="clipboard-list" :noPadding="true">
    <div class="p-4 border-b border-border">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Search</label>
                <input name="q" value="{{ request('q') }}" class="w-full px-3 py-2 rounded-lg border border-border bg-background" placeholder="action, user, ip, description" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Action</label>
                <input name="action" value="{{ request('action') }}" class="w-full px-3 py-2 rounded-lg border border-border bg-background" placeholder="login, logout, status_change" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted-foreground mb-1">Entity Type</label>
                <input name="entity_type" value="{{ request('entity_type') }}" class="w-full px-3 py-2 rounded-lg border border-border bg-background" placeholder="order, auth, services" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="admin-btn admin-btn-md admin-btn-primary w-full">Filter</button>
                <a href="{{ route('admin.audit-logs') }}" class="admin-btn admin-btn-md admin-btn-outline">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-sm text-muted-foreground">
                        {{ isset($log->created_at) ? date('M d, Y H:i', strtotime($log->created_at)) : 'N/A' }}
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $log->user_name ?? 'System' }}</span>
                            @if(!empty($log->user_email))
                                <span class="text-xs text-muted-foreground">{{ $log->user_email }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <x-admin.badge variant="secondary">{{ $log->action }}</x-admin.badge>
                    </td>
                    <td class="text-sm">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $log->entity_type ?? '—' }}</span>
                            <span class="text-xs text-muted-foreground font-mono">{{ $log->entity_id ? substr((string) $log->entity_id, 0, 8) : '—' }}</span>
                        </div>
                    </td>
                    <td class="text-sm">
                        <div class="max-w-[520px]">
                            <div class="font-medium">{{ $log->description }}</div>
                            @if(!empty($log->new_values))
                                @php
                                    $newValuesPretty = null;
                                    $decodedNewValues = json_decode((string) $log->new_values, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $newValuesPretty = json_encode($decodedNewValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                    }
                                @endphp
                                @if(!empty($newValuesPretty))
                                    <pre class="text-xs text-muted-foreground mt-1 whitespace-pre-wrap">{{ $newValuesPretty }}</pre>
                                @else
                                    <div class="text-xs text-muted-foreground mt-1 break-all">{{ $log->new_values }}</div>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="text-sm text-muted-foreground">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state
                            icon="clipboard-list"
                            title="No audit logs found"
                            description="Actions will appear here as users interact with the system." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <x-slot:customFooter>
            <div class="flex justify-center">
                {{ $logs->links() }}
            </div>
        </x-slot:customFooter>
    @endif
</x-admin.card>
@endsection
