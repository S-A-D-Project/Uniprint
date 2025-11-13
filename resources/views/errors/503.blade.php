@extends('layouts.public')

@section('title', 'Service Unavailable')

@section('content')
    <div class="min-h-screen bg-background flex items-center justify-center">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto text-center">
                <div class="mb-8">
                    <i data-lucide="construction" class="h-24 w-24 mx-auto mb-6 text-warning"></i>
                    <h1 class="text-4xl font-bold mb-4">Service Unavailable</h1>
                    <p class="text-lg text-muted-foreground mb-6">
                        UniPrint is currently undergoing maintenance. We'll be back online shortly. Thank you for your patience.
                    </p>
                </div>

                <div class="space-y-4">
                    <button onclick="window.location.reload()" 
                            class="block w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                        <i data-lucide="refresh-cw" class="h-4 w-4 mr-2 inline"></i>
                        Check Again
                    </button>
                </div>

                <div class="mt-8 p-4 bg-muted/30 rounded-lg">
                    <h3 class="font-semibold mb-2">Maintenance in Progress</h3>
                    <p class="text-sm text-muted-foreground mb-3">
                        We're making improvements to serve you better. Estimated completion time: 
                        <span class="font-semibold" id="estimatedTime">calculating...</span>
                    </p>
                    <div class="flex justify-center space-x-4 text-sm">
                        <a href="https://status.uniprint.com" target="_blank" class="text-primary hover:underline">
                            <i data-lucide="activity" class="h-4 w-4 mr-1 inline"></i>
                            Status Page
                        </a>
                        <a href="https://twitter.com/uniprint" target="_blank" class="text-primary hover:underline">
                            <i data-lucide="twitter" class="h-4 w-4 mr-1 inline"></i>
                            Updates
                        </a>
                    </div>
                </div>

                <!-- Progress indicator -->
                <div class="mt-6">
                    <div class="flex justify-center space-x-2">
                        <div class="w-2 h-2 bg-primary rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-primary rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 bg-primary rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
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
        
        // Simulate estimated time (in a real scenario, this would come from the server)
        const estimatedElement = document.getElementById('estimatedTime');
        const estimatedMinutes = Math.floor(Math.random() * 30) + 15; // 15-45 minutes
        estimatedElement.textContent = `${estimatedMinutes} minutes`;
        
        // Auto-refresh every 2 minutes
        setInterval(function() {
            window.location.reload();
        }, 120000);
        
        // Countdown timer
        let countdown = 120; // 2 minutes
        const countdownInterval = setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                return;
            }
            
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            const timeString = minutes > 0 ? `${minutes}:${seconds.toString().padStart(2, '0')}` : `${seconds}s`;
            
            const refreshBtn = document.querySelector('button[onclick="window.location.reload()"]');
            if (refreshBtn && countdown <= 10) {
                refreshBtn.innerHTML = `<i data-lucide="refresh-cw" class="h-4 w-4 mr-2 inline"></i>Auto-refresh in ${timeString}`;
            }
        }, 1000);
    });
</script>
@endpush
