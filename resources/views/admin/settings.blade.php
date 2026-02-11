@extends('layouts.admin-layout')

@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Manage system configuration and maintenance')

@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Settings', 'url' => '#'],
];
@endphp

@section('content')

<!-- System Actions Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <x-admin.card title="Branding" icon="image">
        <p class="text-sm text-muted-foreground mb-4">
            Update the system name and logo shown across the application.
        </p>

        <form action="{{ route('admin.settings.branding') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-2">System name</label>
                <input
                    type="text"
                    name="brand_name"
                    value="{{ old('brand_name', system_brand_name()) }}"
                    class="admin-form-input w-full"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tagline</label>
                <input
                    type="text"
                    name="brand_tagline"
                    value="{{ old('brand_tagline', system_brand_tagline()) }}"
                    class="admin-form-input w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Logo</label>
                <input type="file" name="brand_logo" accept="image/*" class="admin-form-input w-full" />
                @if(system_brand_logo_url())
                    <div class="mt-3">
                        <img src="{{ system_brand_logo_url() }}" alt="{{ system_brand_name() }}" class="h-16 w-16 rounded-lg border border-border object-cover" />
                    </div>
                @endif
                <div class="text-xs text-muted-foreground mt-2">Recommended: square image, at least 256x256.</div>
            </div>

            <x-admin.button type="submit" variant="primary" icon="save" class="w-full">
                Save Branding
            </x-admin.button>
        </form>
    </x-admin.card>

    <!-- Backup Management -->
    <x-admin.card title="Database Backup" icon="database">
        <p class="text-sm text-muted-foreground mb-4">
            Create and manage database backups. Backups are stored locally and automatically cleaned after 7 days.
        </p>
        
        <div class="space-y-3">
            <form action="{{ route('admin.backup.create') }}" method="POST">
                @csrf
                <x-admin.button type="submit" variant="primary" icon="download" class="w-full">
                    Create Backup Now
                </x-admin.button>
            </form>
            
            <div class="flex items-center gap-2 p-3 bg-info/10 rounded-lg text-sm">
                <i data-lucide="info" class="h-4 w-4 text-info"></i>
                <span class="text-info">Last 7 backups are kept automatically</span>
            </div>
        </div>
    </x-admin.card>
    
    <!-- Cache Management -->
    <x-admin.card title="Cache Management" icon="zap">
        <p class="text-sm text-muted-foreground mb-4">
            Clear or optimize application caches to improve performance and apply configuration changes.
        </p>
        
        <div class="space-y-3">
            <form action="{{ route('admin.cache.clear') }}" method="POST">
                @csrf
                <x-admin.button type="submit" variant="outline" icon="trash-2" class="w-full">
                    Clear All Caches
                </x-admin.button>
            </form>
            
            <form action="{{ route('admin.optimize') }}" method="POST">
                @csrf
                <x-admin.button type="submit" variant="success" icon="rocket" class="w-full">
                    Optimize Application
                </x-admin.button>
            </form>
        </div>
    </x-admin.card>

    <!-- Order Automation -->
    <x-admin.card title="Order Automation" icon="clock">
        <p class="text-sm text-muted-foreground mb-4">
            Automatically complete delivered orders if the customer does not confirm within the configured time.
        </p>

        <form action="{{ route('admin.settings.order-auto-complete') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Auto-complete after (hours)</label>
                <input
                    type="number"
                    name="order_auto_complete_hours"
                    min="1"
                    max="720"
                    value="{{ (int) ($autoCompleteHours ?? 72) }}"
                    class="admin-form-input w-full"
                    required
                />
                <div class="text-xs text-muted-foreground mt-2">Default is 72 hours (3 days).</div>
            </div>

            <x-admin.button type="submit" variant="primary" icon="save" class="w-full">
                Save Automation Settings
            </x-admin.button>
        </form>
    </x-admin.card>

    <x-admin.card title="Overdue Orders" icon="alert-triangle">
        <p class="text-sm text-muted-foreground mb-4">
            Configure how the system handles orders that are overdue for too long.
        </p>

        <form action="{{ route('admin.settings.order-overdue-cancel-days') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Auto-cancel after overdue (days)</label>
                <input
                    type="number"
                    name="order_overdue_cancel_days"
                    min="1"
                    max="365"
                    value="{{ (int) ($overdueCancelDays ?? 14) }}"
                    class="admin-form-input w-full"
                    required
                />
                <div class="text-xs text-muted-foreground mt-2">Default is 14 days.</div>
            </div>

            <x-admin.button type="submit" variant="primary" icon="save" class="w-full">
                Save Overdue Settings
            </x-admin.button>
        </form>
    </x-admin.card>

    <!-- Tax Rate -->
    <x-admin.card title="Tax Rate" icon="percent">
        <p class="text-sm text-muted-foreground mb-4">
            Configure the system-wide tax rate used during checkout and pricing.
        </p>

        <form action="{{ route('admin.settings.tax-rate') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2">Tax rate (%)</label>
                <input
                    type="number"
                    name="tax_rate"
                    min="0"
                    max="100"
                    step="0.01"
                    value="{{ (float) ($taxRatePercent ?? 12) }}"
                    class="admin-form-input w-full"
                    required
                />
                <div class="text-xs text-muted-foreground mt-2">Example: 12 for 12% VAT.</div>
            </div>

            <x-admin.button type="submit" variant="primary" icon="save" class="w-full">
                Save Tax Rate
            </x-admin.button>
        </form>
    </x-admin.card>
</div>

<!-- Backup Files List -->
@if(count($backups) > 0)
<x-admin.card title="Available Backups" icon="archive" class="mb-8" :noPadding="true">
    <x-slot:actions>
        <x-admin.badge variant="secondary">{{ count($backups) }} backups</x-admin.badge>
    </x-slot:actions>
    
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($backups as $backup)
                <tr>
                    <td class="font-mono text-sm">{{ $backup['filename'] }}</td>
                    <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                    <td class="text-sm text-muted-foreground">{{ $backup['date'] }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.backup.download', $backup['filename']) }}" 
                               class="admin-btn admin-btn-sm admin-btn-outline inline-flex items-center gap-2">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                Download
                            </a>
                            <form action="{{ route('admin.backup.delete', $backup['filename']) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this backup?')">
                                @csrf
                                @method('DELETE')
                                <x-admin.button type="submit" size="sm" variant="ghost" icon="trash-2" />
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
@else
<x-admin.card>
    <x-admin.empty-state 
        icon="archive"
        title="No backups available"
        description="Create your first database backup to get started" />
</x-admin.card>
@endif

<!-- Danger Zone -->
<x-admin.card title="Danger Zone" icon="alert-triangle" class="border-destructive">
    <x-admin.alert type="warning" class="mb-4">
        <strong>Warning:</strong> The actions below are irreversible. A backup will be created automatically before database reset.
    </x-admin.alert>
    
    <div class="space-y-6">
        <!-- Database Reset -->
        <div class="p-4 border border-destructive/20 rounded-lg bg-destructive/5">
            <h3 class="text-lg font-semibold mb-2 flex items-center gap-2">
                <i data-lucide="rotate-ccw" class="h-5 w-5 text-destructive"></i>
                Reset Database
            </h3>
            <p class="text-sm text-muted-foreground mb-4">
                This will delete all data and reset the database to its initial state with seed data. 
                An automatic backup will be created before reset.
            </p>
            
            <button onclick="showResetModal()" 
                    class="admin-btn admin-btn-destructive admin-btn-md inline-flex items-center gap-2">
                <i data-lucide="alert-triangle" class="h-4 w-4"></i>
                Reset Database
            </button>
        </div>
    </div>
</x-admin.card>

<!-- Reset Confirmation Modal -->
<div id="resetModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-card border border-border rounded-xl shadow-xl max-w-md w-full p-6 animate-slide-up">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0 w-12 h-12 bg-destructive/10 rounded-full flex items-center justify-center">
                <i data-lucide="alert-triangle" class="h-6 w-6 text-destructive"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-1">Confirm Database Reset</h3>
                <p class="text-sm text-muted-foreground">
                    This action cannot be undone. All current data will be deleted.
                </p>
            </div>
        </div>
        
        <form action="{{ route('admin.database.reset') }}" method="POST" id="resetForm">
            @csrf
            <div class="admin-form-group">
                <label class="admin-form-label">
                    Type <span class="font-mono font-bold">RESET</span> to confirm:
                </label>
                <input type="text" 
                       name="confirm" 
                       class="admin-form-input" 
                       placeholder="RESET"
                       required
                       autocomplete="off">
            </div>
            
            <div class="flex items-center gap-3 mt-6">
                <x-admin.button type="button" 
                                variant="outline" 
                                onclick="hideResetModal()"
                                class="flex-1">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit" 
                                variant="destructive" 
                                icon="trash-2"
                                class="flex-1">
                    Reset Database
                </x-admin.button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    
    function showResetModal() {
        document.getElementById('resetModal').classList.remove('hidden');
        document.getElementById('resetModal').classList.add('flex');
    }
    
    function hideResetModal() {
        document.getElementById('resetModal').classList.add('hidden');
        document.getElementById('resetModal').classList.remove('flex');
        document.getElementById('resetForm').reset();
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideResetModal();
        }
    });
    
    // Close modal when clicking outside
    document.getElementById('resetModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideResetModal();
        }
    });
</script>
@endpush
