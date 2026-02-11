<div class="space-y-6 p-6">
    <div class="bg-secondary/30 p-4 rounded-lg flex justify-between items-center">
        <div>
            <p class="text-sm text-muted-foreground">Service Name</p>
            <p class="text-lg font-bold">{{ $service->service_name ?? 'Unnamed Service' }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-muted-foreground">Status</p>
            @if(($service->is_available ?? true))
                <x-admin.badge variant="success" icon="check-circle">Available</x-admin.badge>
            @else
                <x-admin.badge variant="secondary" icon="x-circle">Unavailable</x-admin.badge>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-border">
        <div class="space-y-4">
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Service Info</h4>
                <div class="space-y-2">
                    <p class="text-sm"><span class="text-muted-foreground">ID:</span> <span class="font-mono">{{ $service->service_id }}</span></p>
                    <p class="text-sm"><span class="text-muted-foreground">Base Price:</span> <span class="font-bold text-success">₱{{ number_format($service->base_price ?? 0, 2) }}</span></p>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Enterprise</h4>
                <p class="text-sm font-medium">{{ $service->enterprise_name ?? 'Enterprise' }}</p>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Description</h4>
            <div class="text-sm text-muted-foreground bg-card p-3 rounded-md border border-border min-h-[100px]">
                {{ $service->description_text ?? 'No description available.' }}
            </div>
        </div>
    </div>

    @if(isset($orderCount))
    <div class="pt-4 border-t border-border">
        <div class="p-4 bg-primary/5 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-full">
                    <i data-lucide="shopping-cart" class="h-5 w-5 text-primary"></i>
                </div>
                <span class="text-sm font-medium">Order usage</span>
            </div>
            <span class="text-xl font-bold text-primary">{{ $orderCount }} orders</span>
        </div>
    </div>
    @endif

    <div class="flex justify-end gap-3 pt-6 border-t border-border">
        <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth text-sm font-medium" data-bs-dismiss="modal">
            Close
        </button>
        <a href="{{ route('admin.services.details', $service->service_id) }}" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth text-sm">
            View Full Details
        </a>
    </div>
</div>
