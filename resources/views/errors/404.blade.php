@extends('layouts.public')

@section('title', 'Service Not Found')

@section('content')
    <div class="min-h-screen bg-background flex items-center justify-center">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto text-center">
                <div class="mb-8">
                    <i data-lucide="search-x" class="h-24 w-24 mx-auto mb-6 text-muted-foreground"></i>
                    <h1 class="text-4xl font-bold mb-4">Service Not Found</h1>
                    <p class="text-lg text-muted-foreground mb-6">
                        The printing service you're looking for doesn't exist or may have been removed.
                    </p>
                </div>

                <div class="space-y-4">
                    <a href="{{ route('enterprises.index') }}" 
                       class="block w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                        <i data-lucide="printer" class="h-4 w-4 mr-2 inline"></i>
                        Browse All Services
                    </a>
                    
                    <a href="{{ route('home') }}" 
                       class="block w-full px-6 py-3 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                        <i data-lucide="home" class="h-4 w-4 mr-2 inline"></i>
                        Back to Home
                    </a>
                </div>

                <div class="mt-8 p-4 bg-muted/30 rounded-lg">
                    <h3 class="font-semibold mb-2">Need Help?</h3>
                    <p class="text-sm text-muted-foreground">
                        If you believe this is an error, please contact our support team or try searching for the service again.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Initialize Lucide icons for this page
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
