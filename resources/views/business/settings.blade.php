@extends('layouts.business')

@section('title', 'Settings - ' . ($enterprise->name ?? 'Business'))
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage your account and print shop information')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-lg font-bold mb-4">Account Information</h2>
        <form action="{{ route('business.settings.account.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Position</label>
                    <input type="text" name="position" value="{{ old('position', $user->position ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                    Save Account
                </button>
            </div>
        </form>
    </div>

    <div class="bg-card border border-border rounded-xl shadow-card p-6">
        <h2 class="text-lg font-bold mb-4">Print Shop Information</h2>
        <form action="{{ route('business.settings.enterprise.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-2">Shop Name</label>
                <input type="text" name="name" value="{{ old('name', $enterprise->name ?? '') }}" required
                       class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Address</label>
                <textarea name="address" rows="3"
                          class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">{{ old('address', $enterprise->address ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Shop Email</label>
                    <input type="email" name="email" value="{{ old('email', $enterprise->email ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <input type="text" name="category" value="{{ old('category', $enterprise->category ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $enterprise->contact_person ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $enterprise->contact_number ?? '') }}"
                           class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Shop Logo</label>
                <input type="file" name="shop_logo" accept="image/*"
                       class="w-full text-sm">
                @if(!empty($enterprise->shop_logo))
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $enterprise->shop_logo) }}" alt="Shop Logo" class="h-16 w-16 rounded-lg object-cover border border-border">
                    </div>
                @endif
            </div>

            @if(property_exists($enterprise, 'is_active') || isset($enterprise->is_active))
                <x-ui.form.checkbox
                    name="is_active"
                    id="is_active"
                    :checked="old('is_active', $enterprise->is_active ?? true)"
                    label="Shop is active"
                />
            @endif

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:shadow-glow transition-smooth">
                    Save Print Shop
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
