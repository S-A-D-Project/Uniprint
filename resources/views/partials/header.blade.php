<header class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        <a href="{{ auth()->check() && session('user_id') ? route('customer.dashboard') : route('home') }}" class="flex items-center gap-3">
            <!-- Shop Logo/Branding -->
            <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                <i data-lucide="printer" class="h-6 w-6 text-white"></i>
            </div>
            <div>
                <span class="text-xl font-bold gradient-primary bg-clip-text text-transparent">
                    UniPrint
                </span>
                <p class="text-xs text-foreground/70">Smart Printing Services</p>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-6">
            <a href="{{ route('enterprises.index') }}" class="text-sm font-medium text-foreground hover:text-primary transition-smooth">
                Printing Shops
            </a>
            <a href="{{ route('ai-design.index') }}" class="text-sm font-medium text-foreground hover:text-primary transition-smooth">
                AI Design
            </a>
            @if(session('user_id'))
                <a href="{{ route('customer.orders') }}" class="text-sm font-medium text-foreground hover:text-primary transition-smooth">
                    My Orders
                </a>
            @endif
        </nav>

        <div class="flex items-center gap-3">
            <!-- Mobile Menu Button -->
            <div x-data="{ mobileOpen: false }">
                <button class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth" 
                        @click="mobileOpen = !mobileOpen">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>
                
                <!-- Mobile Menu Overlay -->
                <div x-show="mobileOpen" @click.away="mobileOpen = false" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="md:hidden fixed inset-0 z-50 bg-background/80 backdrop-blur-sm">
                    <div class="fixed inset-y-0 right-0 w-full max-w-sm bg-background border-l border-border shadow-lg">
                        <div class="flex items-center justify-between p-4 border-b border-border">
                            <h2 class="text-lg font-semibold">Menu</h2>
                            <button @click="mobileOpen = false" class="p-2 rounded-md hover:bg-accent">
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>
                        <nav class="p-4 space-y-2">
                            <a href="{{ route('enterprises.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                <i data-lucide="store" class="h-5 w-5"></i>
                                Printing Shops
                            </a>
                            <a href="{{ route('ai-design.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                <i data-lucide="palette" class="h-5 w-5"></i>
                                AI Design
                            </a>
                            @if(session('user_id'))
                                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                    <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('customer.orders') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                    <i data-lucide="package" class="h-5 w-5"></i>
                                    My Orders
                                </a>
                                <a href="{{ route('customer.saved-services') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                    <i data-lucide="heart" class="h-5 w-5"></i>
                                    Saved Services
                                </a>
                                <div class="border-t border-border my-2"></div>
                                <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                    <i data-lucide="settings" class="h-5 w-5"></i>
                                    Settings
                                </a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth text-destructive">
                                        <i data-lucide="log-out" class="h-5 w-5"></i>
                                        Logout
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                                    <i data-lucide="log-in" class="h-5 w-5"></i>
                                    Sign In
                                </a>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>

            @if(session('user_id'))
                <!-- Saved Services Button (replaces Cart) -->
                <a href="{{ route('customer.saved-services') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-border hover:bg-accent hover:text-accent-foreground transition-smooth relative">
                    <i data-lucide="heart" class="h-5 w-5"></i>
                    <span class="hidden md:inline text-sm font-medium">Saved Services</span>
                    @php
                        $savedServicesCount = \App\Models\SavedService::where('user_id', session('user_id'))->count();
                    @endphp
                    @if($savedServicesCount > 0)
                        <span class="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center saved-services-count">
                            {{ $savedServicesCount }}
                        </span>
                    @endif
                </a>

                <!-- Notifications -->
                @php
                    $unreadNotificationsCount = \Illuminate\Support\Facades\Schema::hasTable('order_notifications')
                        ? \Illuminate\Support\Facades\DB::table('order_notifications')
                            ->where('recipient_id', session('user_id'))
                            ->where('is_read', false)
                            ->count()
                        : 0;
                @endphp
                <a href="{{ route('customer.notifications') }}" class="inline-flex items-center justify-center h-10 w-10 rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth relative" aria-label="Notifications">
                    <i data-lucide="bell" class="h-5 w-5"></i>
                    @if($unreadNotificationsCount > 0)
                        <span class="absolute -top-1 -right-1 bg-destructive text-white text-xs font-bold rounded-full h-4 min-w-4 px-1 flex items-center justify-center">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- Profile Icon with Avatar -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                        @php
                            $user = DB::table('users')->where('user_id', session('user_id'))->first();
                            $fullName = $user ? $user->name : 'User';
                            $initials = $user ? strtoupper(substr($user->name, 0, 2)) : 'U';
                        @endphp
                        <!-- Avatar with Initials -->
                        <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ $initials }}
                        </div>
                        <span class="hidden md:inline text-sm font-medium">{{ $fullName }}</span>
                        <i data-lucide="chevron-down" class="h-4 w-4"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-56 bg-popover border border-border rounded-lg shadow-card-hover overflow-hidden">
                        <!-- Profile Header -->
                        <div class="px-4 py-3 border-b border-border">
                            <p class="text-sm font-medium">{{ $fullName }}</p>
                            <p class="text-xs text-muted-foreground">{{ $user->email ?? '' }}</p>
                        </div>
                        <!-- Menu Items - Only Settings and Logout for authenticated users -->
                        <a href="{{ route('profile.index') }}" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-accent transition-smooth">
                            <i data-lucide="settings" class="h-4 w-4 text-muted-foreground"></i>
                            <div>
                                <div class="font-medium">Settings</div>
                                <div class="text-xs text-muted-foreground">Manage your account</div>
                            </div>
                        </a>
                        <div class="border-t border-border"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-3 text-sm text-destructive hover:bg-accent transition-smooth">
                                <i data-lucide="log-out" class="h-4 w-4"></i>
                                <div>
                                    <div class="font-medium">Logout</div>
                                    <div class="text-xs text-muted-foreground">Sign out of your account</div>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Profile Management Icon for Unauthenticated Users -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-2 rounded-md hover:bg-accent transition-smooth">
                        <div class="w-8 h-8 bg-muted rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="h-5 w-5 text-muted-foreground"></i>
                        </div>
                        <span class="hidden md:inline text-sm font-medium">Account</span>
                        <i data-lucide="chevron-down" class="h-4 w-4"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-popover border border-border rounded-lg shadow-card-hover overflow-hidden">
                        <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-accent transition-smooth">
                            <i data-lucide="log-in" class="h-4 w-4 text-primary"></i>
                            <div>
                                <div class="font-medium">Sign In</div>
                                <div class="text-xs text-muted-foreground">Access your account</div>
                            </div>
                        </a>
                        <div class="border-t border-border"></div>
                        <a href="{{ route('enterprises.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent transition-smooth">
                            <i data-lucide="store" class="h-4 w-4"></i>
                            Browse Services
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>

