@extends('layouts.business')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')
@section('page-subtitle', 'Update service information')

@section('header-actions')
<a href="{{ route('business.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-input rounded-lg hover:bg-secondary transition-smooth">
    <i data-lucide="arrow-left" class="h-4 w-4"></i>
    Back to Services
</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <form action="{{ route('business.services.update', $service->service_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Service Name *</label>
                    <input type="text" name="service_name" value="{{ old('service_name', $service->service_name) }}" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('description', $service->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Base Price (₱) *</label>
                    <input type="number" name="base_price" value="{{ old('base_price', $service->base_price) }}" step="0.01" min="0" required
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $service->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-primary rounded focus:ring-2 focus:ring-ring">
                    <label for="is_active" class="text-sm font-medium">Active</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('business.services.index') }}" 
                       class="flex-1 px-6 py-3 text-center border border-input rounded-lg hover:bg-secondary transition-smooth">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                        Update Service
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
