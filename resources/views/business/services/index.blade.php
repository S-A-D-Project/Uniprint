@extends('layouts.business')

@section('title', 'Services - ' . $enterprise->name)
@section('page-title', 'Service Management')
@section('page-subtitle', 'Manage your services')

@section('header-actions')
<a href="{{ route('business.services.create') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
    <i data-lucide="plus" class="h-4 w-4"></i>
    Add Service
</a>
@endsection

@section('content')

    <!-- Services Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
            <div class="bg-card border border-border rounded-xl shadow-card hover:shadow-card-hover transition-smooth overflow-hidden">
                <div class="h-48 gradient-accent flex items-center justify-center">
                    <i data-lucide="package" class="h-24 w-24 text-white"></i>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-lg">{{ $service->service_name }}</h3>
                        @if($service->is_active)
                            <span class="inline-block px-2 py-1 text-xs bg-success/10 text-success rounded-md">Active</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs bg-destructive/10 text-destructive rounded-md">Inactive</span>
                        @endif
                    </div>
                    
                    <p class="text-muted-foreground text-sm mb-4 line-clamp-2">
                        {{ $service->description ?? 'No description' }}
                    </p>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Base Price</p>
                            <p class="text-2xl font-bold text-primary">₱{{ number_format($service->base_price, 2) }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-border">
                        <div>
                            <p class="text-xs text-muted-foreground">Orders</p>
                            <p class="font-semibold">{{ $service->order_count }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Customizations</p>
                            <p class="font-semibold">{{ $service->customization_count }}</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('business.customizations.index', $service->service_id) }}" 
                           class="flex-1 px-3 py-2 text-sm text-center border border-input rounded-md hover:bg-secondary transition-smooth">
                            Customizations
                        </a>
                        <a href="{{ route('business.services.edit', $service->service_id) }}" 
                           class="flex-1 px-3 py-2 text-sm text-center bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                            Edit
                        </a>
                        <form action="{{ route('business.services.delete', $service->service_id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this service?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-sm bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-card border border-border rounded-xl shadow-card p-12 text-center">
                <i data-lucide="package" class="h-24 w-24 mx-auto mb-4 text-muted-foreground"></i>
                <h3 class="text-xl font-bold mb-2">No Services Yet</h3>
                <p class="text-muted-foreground mb-6">Start by adding your first service</p>
                <a href="{{ route('business.services.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Add Service
                </a>
            </div>
        @endforelse
    </div>
    
    @if($services->hasPages())
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection
