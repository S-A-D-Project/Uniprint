@extends('layouts.public')

@section('title', 'Server Error')

@section('content')
    <div class="min-h-screen bg-background flex items-center justify-center">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto text-center">
                <div class="mb-8">
                    <i data-lucide="server-crash" class="h-24 w-24 mx-auto mb-6 text-destructive"></i>
                    <h1 class="text-4xl font-bold mb-4">Server Error</h1>
                    <p class="text-lg text-muted-foreground mb-6">
                        Something went wrong on our end. We're working to fix this issue. Please try again later.
                    </p>
                </div>

                <div class="space-y-4">
                    <button onclick="window.location.reload()" 
                            class="block w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                        <i data-lucide="refresh-cw" class="h-4 w-4 mr-2 inline"></i>
                        Try Again
                    </button>
                    
                    <a href="{{ route('home') }}" 
                       class="block w-full px-6 py-3 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                        <i data-lucide="home" class="h-4 w-4 mr-2 inline"></i>
                        Back to Home
                    </a>
                </div>

                <div class="mt-8 p-4 bg-muted/30 rounded-lg">
                    <h3 class="font-semibold mb-2">Error Code: 500</h3>
                    <p class="text-sm text-muted-foreground mb-3">
                        If this problem persists, please contact our support team and include the error code above.
                    </p>
                    <div class="flex justify-center space-x-4 text-sm">
                        <a href="mailto:support@uniprint.com" class="text-primary hover:underline">
                            <i data-lucide="mail" class="h-4 w-4 mr-1 inline"></i>
                            Email Support
                        </a>
                        <a href="tel:+1234567890" class="text-primary hover:underline">
                            <i data-lucide="phone" class="h-4 w-4 mr-1 inline"></i>
                            Call Support
                        </a>
                    </div>
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
        
        // Auto-retry after 30 seconds
        setTimeout(function() {
            const retryBtn = document.querySelector('button[onclick="window.location.reload()"]');
            if (retryBtn) {
                retryBtn.innerHTML = '<i data-lucide="refresh-cw" class="h-4 w-4 mr-2 inline animate-spin"></i>Auto-retrying...';
                setTimeout(() => window.location.reload(), 2000);
            }
        }, 30000);
    });
</script>
@endpush
