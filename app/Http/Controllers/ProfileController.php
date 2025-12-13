<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function index()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to view your profile');
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'User not found');
            }

            // Get user's role information
            $roleInfo = DB::table('roles')
                ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->where('roles.user_id', $userId)
                ->first();

            // Get user's enterprise information (if business user)
            $enterprise = null;
            if ($roleInfo && $roleInfo->user_role_type === 'business_user') {
                $enterprise = DB::table('staff')
                    ->join('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
                    ->where('staff.user_id', $userId)
                    ->first();
            }

            // Get order statistics
            $orderStats = DB::table('customer_orders')
                ->where('customer_id', $userId)
                ->selectRaw('
                    COUNT(*) as total_orders,
                    SUM(total) as total_spent,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_orders
                ')
                ->first();

            // Get recent orders
            $recentOrders = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id ORDER BY latest_time DESC) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*',
                    'enterprises.name as enterprise_name',
                    'statuses.status_name'
                )
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(5)
                ->get();

            return view('profile.index', compact('user', 'roleInfo', 'enterprise', 'orderStats', 'recentOrders'));
            
        } catch (\Exception $e) {
            Log::error('Profile View Error: ' . $e->getMessage());
            
            return view('profile.index', [
                'user' => null,
                'roleInfo' => null,
                'enterprise' => null,
                'orderStats' => null,
                'recentOrders' => collect()
            ])->with('error', 'Failed to load profile information');
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            // Check if email is already taken by another user
            $existingUser = DB::table('users')
                ->where('email', $request->email)
                ->where('user_id', '!=', $userId)
                ->first();
            
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already taken by another user'
                ], 422);
            }

            // Update user profile
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'bio' => $request->bio,
                'updated_at' => now()
            ];

            DB::table('users')
                ->where('user_id', $userId)
                ->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Profile Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Password Update Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 500);
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = 'profile-' . $userId . '-' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store file
                $path = $file->storeAs('profile-pictures', $filename, 'public');
                
                // Update user profile picture
                DB::table('users')
                    ->where('user_id', $userId)
                    ->update([
                        'profile_picture' => $path,
                        'updated_at' => now()
                    ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile picture updated successfully!',
                    'profile_picture_url' => Storage::url($path)
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Profile Picture Upload Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture'
            ], 500);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect'
                ], 422);
            }

            // Soft delete user (mark as inactive)
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'is_active' => false,
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            // Logout user
            session()->forget('user_id');
            session()->forget('user_role');

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Account Deletion Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account'
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function getNotifications()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $notifications = DB::table('notifications')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $unreadCount = DB::table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Notifications Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $updated = DB::table('notifications')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Mark Notification Read Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }
}
