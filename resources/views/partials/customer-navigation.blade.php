{{-- Customer Navigation Tabs Component --}}
<nav class="flex space-x-8 overflow-x-auto scrollbar-hide" role="tablist">
    {{-- Services Catalog Tab --}}
    <a href="{{ route('customer.dashboard') }}" 
       class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.dashboard') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
        <i data-lucide="shopping-bag" class="h-4 w-4"></i>
        Services Catalog
    </a>
    
    {{-- My Orders Tab --}}
    <a href="{{ route('customer.orders') }}" 
       class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.orders') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
        <i data-lucide="package" class="h-4 w-4"></i>
        My Orders
    </a>
    
    {{-- Design Assets Tab --}}
    <a href="{{ route('customer.design-assets') }}" 
       class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.design-assets') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
        <i data-lucide="image" class="h-4 w-4"></i>
        Design Assets
    </a>
    
    {{-- Saved Services Tab --}}
    <a href="{{ route('customer.saved-services') }}" 
       class="py-4 px-6 border-b-2 {{ request()->routeIs('customer.saved-services') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
        <i data-lucide="heart" class="h-4 w-4"></i>
        Saved Services
        @php
            $savedServicesCount = \App\Models\SavedService::where('user_id', session('user_id'))->count();
        @endphp
        @if($savedServicesCount > 0)
            <span class="bg-primary text-primary-foreground text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center ml-1">
                {{ $savedServicesCount }}
            </span>
        @endif
    </a>
    
    {{-- Account Settings Tab --}}
    <a href="{{ route('profile.index') }}" 
       class="py-4 px-6 border-b-2 {{ request()->routeIs('profile.index') ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }} font-medium text-sm whitespace-nowrap flex items-center gap-2 transition-colors">
        <i data-lucide="user" class="h-4 w-4"></i>
        Account Settings
    </a>
</nav>
