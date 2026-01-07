@extends('layouts.public')

@section('title', 'Business Setup')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Set up your print shop</h1>
                <p class="text-gray-600 mt-1">Create your enterprise profile to access the business dashboard.</p>
            </div>

            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('business.onboarding.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Shop Name</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="address">Address</label>
                    <textarea id="address" name="address" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Shop Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_number">Contact Number</label>
                        <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('contact_number')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_person">Contact Person</label>
                        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('contact_person')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="category">Category</label>
                        <input id="category" name="category" type="text" value="{{ old('category') }}" placeholder="e.g., Printing Services"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('category')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-primary/90 transition">
                        Create Business Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
