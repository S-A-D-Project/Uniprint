<header class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        @php
            $headerRoleType = null;
            $showBusinessVerifyCta = false;
            $isBusinessVerified = true;
            try {
                $u = \Illuminate\Support\Facades\Auth::user();
                if ($u && method_exists($u, 'getUserRoleType')) {
                    $headerRoleType = $u->getUserRoleType();

                    if ($headerRoleType === 'business_user') {
                        $uid = $u->user_id;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id') && \Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'is_verified')) {
                            $enterpriseQuery = \Illuminate\Support\Facades\DB::table('enterprises')->where('owner_user_id', $uid);
                            if ($enterpriseQuery->exists()) {
                                $isBusinessVerified = (bool) $enterpriseQuery->value('is_verified');
                            } elseif (\Illuminate\Support\Facades\Schema::hasTable('staff')) {
                                $enterpriseId = \Illuminate\Support\Facades\DB::table('staff')->where('user_id', $uid)->value('enterprise_id');
                                if ($enterpriseId) {
                                    $isBusinessVerified = (bool) \Illuminate\Support\Facades\DB::table('enterprises')->where('enterprise_id', $enterpriseId)->value('is_verified');
                                }
                            }
                        }

                        if (! $isBusinessVerified) {
                            $showBusinessVerifyCta = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $headerRoleType = null;
                $showBusinessVerifyCta = false;
                $isBusinessVerified = true;
            }

            if (! $headerRoleType && session('user_id')) {
                try {
                    $headerRoleType = \Illuminate\Support\Facades\DB::table('roles')
                        ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                        ->where('roles.user_id', session('user_id'))
                        ->value('role_types.user_role_type');
                } catch (\Throwable $e) {
                    $headerRoleType = null;
                }
            }

            $brandHref = route('home');
            if (session('user_id') && $headerRoleType === 'customer') {
                $brandHref = route('customer.dashboard');
            } elseif (session('user_id') && $headerRoleType === 'admin') {
                $brandHref = route('admin.dashboard');
            } elseif (session('user_id') && $headerRoleType === 'business_user' && $isBusinessVerified) {
                $brandHref = route('business.dashboard');
            }
        @endphp

        <a href="{{ $brandHref }}" class="flex items-center gap-3">
            <!-- Shop Logo/Branding -->
            @php
                $systemBrandLogoUrl = system_brand_logo_url();
            @endphp
            @if($systemBrandLogoUrl)
                <img src="{{ $systemBrandLogoUrl }}" alt="{{ system_brand_name() }}" class="w-10 h-10 rounded-lg object-cover border border-border" />
            @else
                <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                    <i data-lucide="printer" class="h-6 w-6 text-white"></i>
                </div>
            @endif
            <div>
                <span class="text-xl font-bold gradient-primary bg-clip-text text-transparent">
                    {{ system_brand_name() }}
                </span>
                <p class="text-xs text-foreground/70">{{ system_brand_tagline() }}</p>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-6">
            <a href="{{ route('enterprises.index') }}" class="text-sm font-medium text-foreground hover:text-primary transition-smooth">
                Printing Shops
            </a>
            <a href="{{ route('ai-design.index') }}" class="text-sm font-medium text-foreground hover:text-primary transition-smooth">
                AI Design
            </a>
            @if(session('user_id') && $headerRoleType === 'customer')
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
                            @if($showBusinessVerifyCta)
                                <a href="{{ route('business.verification') }}" class="flex items-center gap-3 px-3 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth">
                                    <i data-lucide="badge-check" class="h-5 w-5"></i>
                                    Verify now
                                </a>
                            @endif
                            @if(session('user_id'))
                                @if($headerRoleType === 'customer')
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
                                @endif
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

            @if($showBusinessVerifyCta)
                <a href="{{ route('business.verification') }}" class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-smooth">
                    <i data-lucide="badge-check" class="h-5 w-5"></i>
                    <span class="text-sm font-semibold">Verify now</span>
                </a>
            @endif

            @if(session('user_id'))
                @if($headerRoleType === 'customer')
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
                @endif

                <!-- Notifications -->
                @php
                    $roleType = $headerRoleType;

                    $notificationsRouteName = 'customer.notifications';
                    $notificationsReadRouteName = 'customer.notifications.read';
                    if ($roleType === 'business_user') {
                        $notificationsRouteName = 'business.notifications';
                        $notificationsReadRouteName = 'business.notifications.read';
                    } elseif ($roleType === 'admin') {
                        $notificationsRouteName = 'profile.notifications';
                        $notificationsReadRouteName = 'profile.notifications.read';
                    }

                    $unreadNotificationsCount = 0;
                    if ($roleType === 'admin') {
                        $unreadNotificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
                            ? \Illuminate\Support\Facades\DB::table('notifications')
                                ->where('user_id', session('user_id'))
                                ->where('is_read', false)
                                ->count()
                            : 0;
                    } else {
                        $unreadNotificationsCount = \Illuminate\Support\Facades\Schema::hasTable('order_notifications')
                            ? \Illuminate\Support\Facades\DB::table('order_notifications')
                                ->where('recipient_id', session('user_id'))
                                ->where('is_read', false)
                                ->count()
                            : 0;
                    }
                @endphp
                <div x-data="notificationsModal()" class="relative">
                    <button type="button"
                            @click="open()"
                            class="inline-flex items-center justify-center h-10 w-10 rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth relative"
                            aria-label="Notifications"
                            data-notifications-url="{{ route($notificationsRouteName, [], false) }}"
                            data-notification-read-url-template="{{ route($notificationsReadRouteName, ['id' => '___ID___'], false) }}">
                        <i data-lucide="bell" class="h-5 w-5"></i>
                        <span class="absolute -top-1 -right-1 bg-destructive text-white text-xs font-bold rounded-full h-4 min-w-4 px-1 flex items-center justify-center"
                              x-ref="badge" @if($unreadNotificationsCount <= 0) style="display: none;" @endif>
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    </button>

                    <div x-show="isOpen" x-transition.opacity
                         class="fixed inset-0 z-50"
                         style="display: none;">
                        <div class="absolute inset-0 bg-black/40" @click="close()"></div>

                        <div class="absolute right-4 top-20 w-[92vw] max-w-lg bg-popover border border-border rounded-xl shadow-card-hover overflow-hidden">
                            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="bell" class="h-5 w-5 text-muted-foreground"></i>
                                    <h3 class="text-sm font-semibold">Notifications</h3>
                                </div>
                                <button type="button" class="p-2 rounded-md hover:bg-accent" @click="close()" aria-label="Close notifications">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>

                            <div class="max-h-[70vh] overflow-auto">
                                <template x-if="loadedOnce && items.length === 0">
                                    <div class="p-8 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-accent/50 mb-4">
                                            <i data-lucide="bell-off" class="h-7 w-7 text-muted-foreground"></i>
                                        </div>
                                        <p class="font-semibold">No notifications</p>
                                        <p class="text-sm text-muted-foreground">You're all caught up.</p>
                                    </div>
                                </template>

                                <template x-for="item in items" :key="item.notification_id">
                                    <div class="p-4 border-b border-border flex gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                                             :class="iconBg(item.notification_type)">
                                            <i :data-lucide="iconName(item.notification_type)" class="h-5 w-5" :class="iconColor(item.notification_type)"></i>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <div class="text-sm font-semibold truncate" x-text="item.title"></div>
                                                        <span class="inline-block w-2 h-2 bg-primary rounded-full" x-show="!item.is_read"></span>
                                                    </div>
                                                    <div class="text-xs text-muted-foreground mt-1" x-text="item.message"></div>
                                                    <div class="text-[11px] text-muted-foreground mt-2" x-text="formatTime(item.created_at)"></div>
                                                </div>

                                                <button type="button"
                                                        class="px-3 py-1 text-xs border border-input rounded-md hover:bg-secondary"
                                                        x-show="!item.is_read"
                                                        @click="markRead(item)">
                                                    Mark Read
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="px-4 py-3 border-t border-border flex items-center justify-between">
                                <div></div>
                                <button type="button" class="text-xs text-muted-foreground hover:text-foreground transition-smooth" @click="refresh()">Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>

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

<script>
    function notificationsModal() {
        return {
            isOpen: false,
            loading: false,
            loadedOnce: false,
            items: [],
            unreadCount: null,
            pollId: null,
            normalizeUrl(url) {
                try {
                    const u = new URL(url, window.location.href);
                    return u.pathname + u.search + u.hash;
                } catch (e) {
                    return url;
                }
            },
            init() {
                window.addEventListener('open-notifications', () => {
                    this.open();
                });
            },
            open() {
                this.isOpen = true;
                this.refresh();
                this.startPolling();
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },
            close() {
                this.isOpen = false;
                this.stopPolling();
            },
            startPolling() {
                this.stopPolling();
                this.pollId = window.setInterval(() => {
                    if (this.isOpen) {
                        this.refresh();
                    }
                }, 8000);
            },
            stopPolling() {
                if (this.pollId) {
                    window.clearInterval(this.pollId);
                    this.pollId = null;
                }
            },
            notificationsUrl() {
                return this.$el.querySelector('[data-notifications-url]')?.getAttribute('data-notifications-url');
            },
            readUrl(notificationId) {
                const tpl = this.$el.querySelector('[data-notification-read-url-template]')?.getAttribute('data-notification-read-url-template');
                return tpl ? tpl.replace('___ID___', notificationId) : null;
            },
            async refresh() {
                const url = this.notificationsUrl();
                if (!url) return;
                this.loading = true;
                try {
                    const res = await fetch(this.normalizeUrl(url), {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        this.items = Array.isArray(data.notifications) ? data.notifications : [];
                        this.unreadCount = typeof data.unread_count === 'number' ? data.unread_count : null;
                        this.updateBadge();
                    }
                } catch (e) {
                } finally {
                    this.loading = false;
                    this.loadedOnce = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                }
            },
            async markRead(item) {
                const url = this.readUrl(item.notification_id);
                if (!url) return;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(this.normalizeUrl(url), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(token ? { 'X-CSRF-TOKEN': token } : {})
                        },
                        body: JSON.stringify({})
                    });
                    const data = await res.json();
                    if (data && data.success) {
                        item.is_read = true;
                        if (typeof this.unreadCount === 'number') {
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                        this.updateBadge();
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    }
                } catch (e) {
                }
            },
            updateBadge() {
                const el = this.$refs.badge;
                if (!el) return;
                if (typeof this.unreadCount !== 'number') return;
                if (this.unreadCount <= 0) {
                    el.style.display = 'none';
                    return;
                }
                el.style.display = 'flex';
                el.textContent = this.unreadCount > 9 ? '9+' : String(this.unreadCount);
            },
            iconName(type) {
                if (type === 'status_change') return 'bell';
                if (type === 'file_upload') return 'file';
                return 'message-square';
            },
            iconBg(type) {
                if (type === 'status_change') return 'bg-primary/10';
                if (type === 'file_upload') return 'bg-success/10';
                return 'bg-accent/10';
            },
            iconColor(type) {
                if (type === 'status_change') return 'text-primary';
                if (type === 'file_upload') return 'text-success';
                return 'text-accent';
            },
            formatTime(ts) {
                if (!ts) return '';
                const d = new Date(ts);
                if (Number.isNaN(d.getTime())) return String(ts);
                return d.toLocaleString();
            }
        };
    }
</script>

