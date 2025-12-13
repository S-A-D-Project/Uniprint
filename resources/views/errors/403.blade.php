@extends('layouts.public')

@section('title', 'Access Forbidden')

@section('content')
    <div class="min-h-screen bg-background flex items-center justify-center">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto text-center">
                <div class="mb-8">
                    <i data-lucide="shield-x" class="h-24 w-24 mx-auto mb-6 text-destructive"></i>
                    <h1 class="text-4xl font-bold mb-4">Access Forbidden</h1>
                    <p class="text-lg text-muted-foreground mb-6">
                        You don't have permission to access this resource. Please contact your administrator if you believe this is an error.
                    </p>
                </div>

                <div class="space-y-4">
                    @auth
                        <a href="{{ route(auth()->user()->role_type === 'admin' ? 'admin.dashboard' : (auth()->user()->role_type === 'business_user' ? 'business.dashboard' : 'customer.dashboard')) }}" 
                           class="block w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            <i data-lucide="home" class="h-4 w-4 mr-2 inline"></i>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                            <i data-lucide="log-in" class="h-4 w-4 mr-2 inline"></i>
                            Login
                        </a>
                    @endauth
                    
                    <a href="{{ route('home') }}" 
                       class="block w-full px-6 py-3 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                        <i data-lucide="home" class="h-4 w-4 mr-2 inline"></i>
                        Back to Home
                    </a>
                </div>

                <div class="mt-8 p-4 bg-muted/30 rounded-lg">
                    <h3 class="font-semibold mb-2">Need Help?</h3>
                    <p class="text-sm text-muted-foreground">
                        If you believe you should have access to this resource, please contact our support team for assistance.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
