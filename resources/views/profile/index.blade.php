@extends('layouts.public')

@section('title', 'Account Settings')

@section('content')

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
                    <span class="text-gray-600">Total Spent:</span>
                    <span class="font-semibold text-primary">₱{{ number_format($orderStats->total_spent ?? 0, 2) }}</span>
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

        <!-- Security Settings -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock text-primary me-2"></i>
                    Security Settings
                </h5>
            </div>
            <div class="card-body">
                <form id="password-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="col-md-4">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-4">
                            <label for="new_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="new_password_confirmation" 
                                   name="new_password_confirmation" minlength="8" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning" id="update-password-btn">
                            <i class="bi bi-shield-check me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    Recent Orders
                </h5>
                <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Enterprise</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_no ?? substr($order->purchase_order_id, 0, 8) }}</strong></td>
                                    <td>{{ $order->enterprise_name }}</td>
                                    <td class="text-primary">₱{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $order->status_name ?? 'Pending' }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cart-x text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No orders yet</p>
                        <a href="{{ route('customer.enterprises') }}" class="btn btn-primary btn-sm">
                            Start Shopping
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 mb-2">Profile Not Found</h4>
    <p class="text-muted mb-4">Please login to view your profile</p>
    <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
    </a>
</div>
@endif

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone. Deleting your account will:
                </div>
                <ul>
                    <li>Permanently delete your profile information</li>
                    <li>Remove access to your order history</li>
                    <li>Delete all saved designs and files</li>
                    <li>Cancel any pending orders</li>
                </ul>
                <form id="delete-account-form">
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="delete_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="delete_confirmation" class="form-label">Type "DELETE" to confirm</label>
                        <input type="text" class="form-control" id="delete_confirmation" name="confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="bi bi-trash me-2"></i>Delete Account
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update profile
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('save-profile-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const response = await fetch('{{ route("profile.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

// Update password
document.getElementById('password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('update-password-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
    
    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        const response = await fetch('{{ route("profile.update-password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

// Upload profile picture
document.getElementById('profile-picture-input').addEventListener('change', async function(e) {
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
document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
    const password = document.getElementById('delete_password').value;
    const confirmation = document.getElementById('delete_confirmation').value;
    
    if (!password || confirmation !== 'DELETE') {
        showToast('Please fill in all fields correctly', 'warning');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Deleting...';
    
    try {
        const response = await fetch('{{ route("profile.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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
        btn.innerHTML = '<i class="bi bi-trash me-2"></i>Delete Account';
    }
});

// Toast notification helper
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'success'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    setTimeout(() => {
        document.body.removeChild(toastContainer);
    }, 5000);
}
</script>
@endpush
