@php
    $isActive = isset($enterprise->is_active) ? (bool) $enterprise->is_active : true;
    $isVerified = isset($enterprise->is_verified) ? (bool) $enterprise->is_verified : true;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card title="Enterprise" icon="building-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-muted-foreground">Enterprise ID</div>
                    <div class="font-mono font-semibold">{{ $enterprise->enterprise_id ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Status</div>
                    <div class="mt-1">
                        @if($isActive)
                            <x-admin.badge variant="success" icon="check-circle">Active</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="x-circle">Inactive</x-admin.badge>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Verification</div>
                    <div class="mt-1">
                        @if($isVerified)
                            <x-admin.badge variant="success" icon="check-circle">Verified</x-admin.badge>
                        @else
                            <x-admin.badge variant="secondary" icon="clock">Pending</x-admin.badge>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Name</div>
                    <div class="font-semibold">{{ $enterprise->name ?? 'Unknown Enterprise' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">TIN</div>
                    <div class="font-semibold">{{ $enterprise->tin_no ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Contact Person</div>
                    <div class="font-semibold">{{ $enterprise->contact_person ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-muted-foreground">Contact Number</div>
                    <div class="font-semibold">{{ $enterprise->contact_number ?? '—' }}</div>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'verification_document_path'))
                <div class="mt-6 border-t border-border pt-5">
                    <div class="text-sm font-semibold mb-3">Verification Proof</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Submitted</div>
                            <div class="font-semibold">
                                @if(!empty($enterprise->verification_submitted_at))
                                    {{ is_string($enterprise->verification_submitted_at) ? date('M d, Y H:i', strtotime($enterprise->verification_submitted_at)) : \Carbon\Carbon::parse($enterprise->verification_submitted_at)->format('M d, Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Document</div>
                            <div class="font-semibold">
                                @if(!empty($enterprise->verification_document_path))
                                    <a class="text-primary hover:text-primary/80" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($enterprise->verification_document_path) }}" target="_blank" rel="noopener">View document</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card title="Performance" icon="bar-chart-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Services</div>
                    <div class="text-2xl font-bold">{{ $stats['services_count'] ?? 0 }}</div>
                </div>
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Orders</div>
                    <div class="text-2xl font-bold">{{ $stats['orders_count'] ?? 0 }}</div>
                </div>
                <div class="p-4 bg-success/10 rounded-lg">
                    <div class="text-sm text-muted-foreground">Revenue</div>
                    <div class="text-2xl font-bold text-success">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                </div>
                <div class="p-4 bg-secondary/30 rounded-lg">
                    <div class="text-sm text-muted-foreground">Staff</div>
                    <div class="text-2xl font-bold">{{ $stats['staff_count'] ?? 0 }}</div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Quick Actions" icon="settings">
            <div class="space-y-3">
                @if(!$isVerified)
                    <form method="POST" action="{{ route('admin.enterprises.verify', $enterprise->enterprise_id) }}">
                        @csrf
                        <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Approve Verification</x-admin.button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.enterprises.toggle-active', $enterprise->enterprise_id) }}">
                    @csrf
                    @if($isActive)
                        <x-admin.button type="submit" variant="destructive" icon="x-circle" class="w-full">Deactivate Enterprise</x-admin.button>
                    @else
                        <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Activate Enterprise</x-admin.button>
                    @endif
                </form>

                <x-admin.button variant="outline" icon="package" href="{{ route('admin.services', ['enterprise_id' => $enterprise->enterprise_id]) }}" class="w-full">View Services</x-admin.button>
                <x-admin.button variant="outline" icon="shopping-cart" href="{{ route('admin.orders', ['enterprise_id' => $enterprise->enterprise_id]) }}" class="w-full">View Orders</x-admin.button>
            </div>
        </x-admin.card>
    </div>
</div>
