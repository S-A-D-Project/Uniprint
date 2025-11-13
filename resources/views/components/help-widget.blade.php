<!-- Help Widget - TailwindCSS Version -->
<div class="fixed bottom-6 right-6 z-50">
    <!-- Help Button -->
    <button id="help-toggle" class="w-14 h-14 rounded-full gradient-primary shadow-glow flex items-center justify-center text-white hover:scale-110 transition-smooth">
        <i data-lucide="message-circle" class="h-6 w-6"></i>
    </button>
    
    <!-- Help Menu -->
    <div id="help-menu" class="hidden absolute bottom-20 right-0 w-80 bg-card border border-border rounded-xl shadow-card-hover p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Need Help?</h3>
            <button id="help-close" class="text-muted-foreground hover:text-foreground">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        
        <div class="space-y-3">
            <!-- Quick Links -->
            <a href="{{ route('ai-design.index') }}" class="block p-3 rounded-lg hover:bg-secondary transition-smooth">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i data-lucide="sparkles" class="h-5 w-5 text-primary"></i>
                    </div>
                    <div>
                        <p class="font-medium">AI Design Tool</p>
                        <p class="text-xs text-muted-foreground">Create custom designs</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('customer.orders') }}" class="block p-3 rounded-lg hover:bg-secondary transition-smooth">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                        <i data-lucide="package" class="h-5 w-5 text-success"></i>
                    </div>
                    <div>
                        <p class="font-medium">Track Orders</p>
                        <p class="text-xs text-muted-foreground">View order status</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('profile.index') }}" class="block p-3 rounded-lg hover:bg-secondary transition-smooth">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                        <i data-lucide="user" class="h-5 w-5 text-accent"></i>
                    </div>
                    <div>
                        <p class="font-medium">My Profile</p>
                        <p class="text-xs text-muted-foreground">Account settings</p>
                    </div>
                </div>
            </a>
            
            <!-- Contact Info -->
            <div class="border-t border-border pt-3 mt-3">
                <p class="text-sm font-medium mb-2">Contact Support</p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <i data-lucide="mail" class="h-4 w-4"></i>
                        <span>support@uniprint.com</span>
                    </div>
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <i data-lucide="phone" class="h-4 w-4"></i>
                        <span>(02) 123-4567</span>
                    </div>
                </div>
            </div>
            
            <!-- FAQs -->
            <details class="border-t border-border pt-3">
                <summary class="text-sm font-medium cursor-pointer hover:text-primary">Quick FAQs</summary>
                <div class="mt-2 space-y-2 text-sm text-muted-foreground">
                    <div>
                        <p class="font-medium text-foreground">How do I track my order?</p>
                        <p>Go to "My Orders" to view real-time tracking information.</p>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">What payment methods do you accept?</p>
                        <p>We accept GCash, Bank Transfer, Credit Card, and Cash on Delivery.</p>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">How long does delivery take?</p>
                        <p>Standard: 5-7 days, Express: 2-3 days, Same Day (Metro Manila).</p>
                    </div>
                </div>
            </details>
        </div>
    </div>
</div>

<script>
// Help widget toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('help-toggle');
    const closeBtn = document.getElementById('help-close');
    const menu = document.getElementById('help-menu');
    
    if (toggleBtn && menu) {
        toggleBtn.addEventListener('click', function() {
            menu.classList.toggle('hidden');
            // Reinitialize lucide icons for the menu
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                menu.classList.add('hidden');
            });
        }
        
        // Close when clicking outside
        document.addEventListener('click', function(event) {
            if (!toggleBtn.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    }
});
</script>
