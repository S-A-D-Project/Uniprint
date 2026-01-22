@extends('layouts.public')

@section('title', 'Account Settings')

@section('content')

 <div class="container py-10">

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <i data-lucide="user" class="h-8 w-8 text-primary"></i>
        <h1 class="text-3xl font-bold text-gray-900">Account Settings</h1>
    </div>
    <p class="text-gray-600 text-lg">Manage your account settings and preferences</p>
</div>

@if($user)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Information -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <!-- Profile Picture -->
            <div class="text-center mb-6">
                <div class="relative inline-block">
                    <div class="w-32 h-32 gradient-primary rounded-full flex items-center justify-center text-white text-4xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <button class="absolute bottom-0 right-0 w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center hover:bg-primary/90 transition-colors" 
                            onclick="document.getElementById('profile-picture-input').click()">
                        <i data-lucide="camera" class="h-5 w-5"></i>
                    </button>
                    <input type="file" id="profile-picture-input" accept="image/*" class="hidden">
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 mt-4">{{ $user->name }}</h2>
                <p class="text-gray-600 mb-4">{{ $user->email }}</p>
                
                @if($roleInfo)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                    {{ ucfirst(str_replace('_', ' ', $roleInfo->user_role_type)) }}
                </span>
                @endif
                
                @if($enterprise)
                <div class="mt-4 p-3 bg-muted/30 rounded-lg">
                    <p class="text-sm text-gray-600">Enterprise:</p>
                    <p class="font-semibold text-gray-900">{{ $enterprise->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="trending-up" class="h-5 w-5 text-primary"></i>
                <h3 class="text-lg font-semibold text-gray-900">Order Statistics</h3>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Orders:</span>
                    <span class="font-semibold text-gray-900">{{ $orderStats->total_orders ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Recent (30 days):</span>
                    <span class="font-semibold text-gray-900">{{ $orderStats->recent_orders ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Settings -->
    <div class="lg:col-span-2">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="user-circle" class="h-5 w-5 text-primary"></i>
                <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
            </div>
            <form id="profile-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ $user->email }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ $user->phone ?? '' }}" placeholder="+63 9XX XXX XXXX"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="username" name="username" value="{{ $user->username ?? '' }}" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                        <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
                    </div>
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">{{ $user->address ?? '' }}</textarea>
                </div>
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">{{ $user->bio ?? '' }}</textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" id="save-profile-btn"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                        <i data-lucide="check-circle" class="h-4 w-4"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Connected Accounts -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="link" class="h-5 w-5 text-primary"></i>
                <h3 class="text-lg font-semibold text-gray-900">Connected Accounts</h3>
            </div>

            @php
                $providers = $linkedProviders ?? collect();
                $facebookConnected = $providers->contains('facebook');
            @endphp

            <div class="space-y-4">
                <div class="flex items-center justify-between border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i data-lucide="facebook" class="h-5 w-5 text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Facebook</div>
                            <div class="text-sm text-gray-600">
                                {{ $facebookConnected ? 'Connected' : 'Not connected' }}
                            </div>
                        </div>
                    </div>

                    @if($facebookConnected)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success/10 text-success">
                            Connected
                        </span>
                    @else
                        <a href="{{ route('profile.connect-facebook') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <i data-lucide="link" class="h-4 w-4"></i>
                            Connect
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="shield" class="h-5 w-5 text-primary"></i>
                <h3 class="text-lg font-semibold text-gray-900">Security Settings</h3>
            </div>
            <form id="password-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" minlength="8" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" id="update-password-btn"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-warning text-warning-foreground font-medium rounded-lg hover:opacity-90 transition-colors">
                        <i data-lucide="shield-check" class="h-4 w-4"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="h-5 w-5 text-primary"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                </div>
                <a href="{{ route('customer.orders') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg hover:bg-muted/40 transition-colors">
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    View All
                </a>
            </div>

            @if($recentOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3 pr-4">Order #</th>
                                <th class="py-3 pr-4">Enterprise</th>
                                <th class="py-3 pr-4">Total</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="py-3 pr-4 font-semibold text-gray-900">#{{ $order->order_no ?? substr($order->purchase_order_id, 0, 8) }}</td>
                                    <td class="py-3 pr-4 text-gray-700">{{ $order->enterprise_name }}</td>
                                    <td class="py-3 pr-4 font-semibold text-primary">₱{{ number_format($order->total, 2) }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-secondary text-secondary-foreground">
                                            {{ $order->status_name ?? 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-gray-600">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="mx-auto w-12 h-12 rounded-full bg-muted flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="h-6 w-6 text-muted-foreground"></i>
                    </div>
                    <p class="text-gray-600 mt-3">No orders yet</p>
                    <a href="{{ route('customer.enterprises') }}"
                       class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <i data-lucide="sparkles" class="h-4 w-4"></i>
                        Start Shopping
                    </a>
                </div>
            @endif
        </div>

        <!-- Delete Account -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="triangle-alert" class="h-5 w-5 text-destructive"></i>
                <h3 class="text-lg font-semibold text-gray-900">Delete Account</h3>
            </div>
            <div class="p-4 rounded-lg border border-destructive/20 bg-destructive/5">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold text-destructive">Warning:</span>
                    This action cannot be undone.
                </p>
                <ul class="list-disc pl-5 mt-3 space-y-1 text-sm text-gray-700">
                    <li>Permanently delete your profile information</li>
                    <li>Remove access to your order history</li>
                    <li>Delete all saved designs and files</li>
                    <li>Cancel any pending orders</li>
                </ul>
            </div>
            <form id="delete-account-form" class="mt-4 space-y-4">
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">Enter your password to confirm</label>
                    <input type="password" id="delete_password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-destructive focus:border-transparent transition-colors">
                </div>
                <div>
                    <label for="delete_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Type "DELETE" to confirm</label>
                    <input type="text" id="delete_confirmation" name="confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-destructive focus:border-transparent transition-colors">
                </div>
                <div class="pt-2">
                    <button type="button" id="confirm-delete-btn"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-destructive text-destructive-foreground font-medium rounded-lg hover:opacity-90 transition-colors">
                        <i data-lucide="trash" class="h-4 w-4"></i>
                        Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
 <div class="max-w-lg mx-auto py-16">
     <div class="bg-white rounded-lg shadow-sm p-8 text-center">
         <div class="mx-auto w-14 h-14 rounded-full bg-muted flex items-center justify-center">
             <i data-lucide="user-x" class="h-7 w-7 text-muted-foreground"></i>
         </div>
         <h2 class="text-xl font-bold text-gray-900 mt-4">Couldn’t load your account settings</h2>
         <p class="text-gray-600 mt-2">Please refresh the page. If this keeps happening, your session may have expired.</p>
         <div class="mt-6 flex items-center justify-center gap-3">
             <a href="{{ route('profile.index') }}"
                class="inline-flex items-center gap-2 px-5 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                 <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                 Refresh
             </a>
             <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 px-5 py-3 border border-gray-200 rounded-lg hover:bg-muted/40 transition-colors">
                 <i data-lucide="home" class="h-4 w-4"></i>
                 Home
             </a>
         </div>
     </div>
 </div>
@endif
 </div>
@endsection

@push('scripts')
<script>
// Update profile
const profileForm = document.getElementById('profile-form');
if (profileForm) profileForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('save-profile-btn');
    const originalText = btn ? btn.innerHTML : '';
    if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
        UniPrintUI.setButtonLoading(btn, true, { text: 'Saving...' });
    } else {
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
    }
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const response = await fetch('{{ route("profile.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
        } else {
            showToast(result.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    } finally {
        if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
            UniPrintUI.setButtonLoading(btn, false);
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
});

// Update password
const passwordForm = document.getElementById('password-form');
if (passwordForm) passwordForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('update-password-btn');
    const originalText = btn ? btn.innerHTML : '';
    if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
        UniPrintUI.setButtonLoading(btn, true, { text: 'Updating...' });
    } else {
        btn.disabled = true;
        btn.innerHTML = 'Updating...';
    }
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const response = await fetch('{{ route("profile.update-password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            this.reset();
        } else {
            showToast(result.message || 'Failed to update password', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    } finally {
        if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
            UniPrintUI.setButtonLoading(btn, false);
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
});

// Upload profile picture
const profilePictureInput = document.getElementById('profile-picture-input');
if (profilePictureInput) profilePictureInput.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) {
        showToast('File size must be less than 2MB', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    try {
        const response = await fetch('{{ route("profile.upload-picture") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            // Update profile picture display
            location.reload();
        } else {
            showToast(result.message || 'Failed to upload profile picture', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    }
});

// Delete account
const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
if (confirmDeleteBtn) confirmDeleteBtn.addEventListener('click', async function() {
    const password = document.getElementById('delete_password').value;
    const confirmation = document.getElementById('delete_confirmation').value;
    
    if (!password || confirmation !== 'DELETE') {
        showToast('Please fill in all fields correctly', 'warning');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = 'Deleting...';
    
    try {
        const response = await fetch('{{ route("profile.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ password, confirmation })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 2000);
        } else {
            showToast(result.message || 'Failed to delete account', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Delete Account';
    }
});

// Toast notification helper
function showToast(message, type = 'info') {
    const colors = {
        success: { bg: 'bg-success', text: 'text-success-foreground' },
        warning: { bg: 'bg-warning', text: 'text-warning-foreground' },
        error: { bg: 'bg-destructive', text: 'text-destructive-foreground' },
        info: { bg: 'bg-secondary', text: 'text-secondary-foreground' },
    };

    const c = colors[type] || colors.info;

    const wrap = document.createElement('div');
    wrap.className = `fixed bottom-4 right-4 z-50 max-w-sm w-[calc(100vw-2rem)] p-4 rounded-lg shadow-card-hover ${c.bg} ${c.text}`;

    wrap.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-1 text-sm">${escapeHtml(message)}</div>
            <button type="button" class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-white/10 transition" aria-label="Close">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
    `;

    const btn = wrap.querySelector('button');
    btn.addEventListener('click', () => wrap.remove());

    document.body.appendChild(wrap);
    try { lucide.createIcons(); } catch (_) {}

    setTimeout(() => {
        wrap.remove();
    }, 5000);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
@endpush
