<footer class="bg-secondary/50 border-t border-border">
    <div class="container mx-auto px-4 py-12">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Brand Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="store" class="h-6 w-6 text-primary"></i>
                    <span class="text-xl font-bold gradient-primary bg-clip-text text-transparent">
                        UniPrint
                    </span>
                </div>
                <p class="text-sm text-muted-foreground">
                    AI-enhanced printing platform connecting customers with local printing shops in Baguio City.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">
                        <i data-lucide="facebook" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">
                        <i data-lucide="twitter" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">
                        <i data-lucide="instagram" class="h-5 w-5"></i>
                    </a>
                </div>
            </div>

            <!-- Services -->
            <div class="space-y-4">
                <h4 class="font-semibold">Services</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Business Cards</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Flyers & Brochures</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Banners & Posters</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Custom T-Shirts</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Large Format Printing</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="space-y-4">
                <h4 class="font-semibold">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('enterprises.index') }}" class="text-muted-foreground hover:text-primary transition-smooth">Browse Shops</a></li>
                    <li><a href="{{ route('login') }}" class="text-muted-foreground hover:text-primary transition-smooth">Sign In</a></li>
                    <li><a href="{{ route('register') }}" class="text-muted-foreground hover:text-primary transition-smooth">Create Account</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Help Center</a></li>
                    <li><a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="space-y-4">
                <h4 class="font-semibold">Contact</h4>
                <div class="space-y-2 text-sm text-muted-foreground">
                    <div class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="h-4 w-4"></i>
                        <span>Baguio City, Philippines</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="phone" class="h-4 w-4"></i>
                        <span>+63 74 123 4567</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="mail" class="h-4 w-4"></i>
                        <span>hello@uniprint.ph</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-border mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-muted-foreground">
                © {{ date('Y') }} UniPrint. All rights reserved.
            </p>
            <div class="flex gap-6 text-sm">
                <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Privacy Policy</a>
                <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Terms of Service</a>
                <a href="#" class="text-muted-foreground hover:text-primary transition-smooth">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>
