<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\SafePropertyAccess;

class AdminController extends Controller
{
    use SafePropertyAccess;

    private function logAudit(string $action, string $entityType, ?string $entityId, string $description, $oldValues = null, $newValues = null): void
    {
        try {
            DB::table('audit_logs')->insert([
                'log_id' => (string) Str::uuid(),
                'user_id' => session('user_id'),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to write audit log', ['error' => $e->getMessage()]);
        }
    }
    public function dashboard()
    {
        try {
            // Commission rate (configurable - could be moved to settings)
            $commissionRate = 0.05; // 5% commission rate

            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $ordersWithLatestStatus = DB::table('customer_orders')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id');

            $stats = Cache::remember('admin.dashboard.stats', 60, function () use ($commissionRate, $ordersWithLatestStatus) {
                $totalOrderValue = DB::table('customer_orders')->sum('total') ?? 0;

                return [
                    'total_users' => DB::table('users')->count() ?? 0,
                    'total_customers' => DB::table('roles')
                        ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                        ->where('role_types.user_role_type', 'customer')
                        ->distinct()
                        ->count('roles.user_id') ?? 0,
                    'total_business_users' => DB::table('roles')
                        ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                        ->where('role_types.user_role_type', 'business_user')
                        ->distinct()
                        ->count('roles.user_id') ?? 0,
                    'total_enterprises' => DB::table('enterprises')->count() ?? 0,
                    'total_services' => DB::table('services')->count() ?? 0,
                    'total_orders' => DB::table('customer_orders')->count() ?? 0,
                    'pending_orders' => (clone $ordersWithLatestStatus)
                        ->where('statuses.status_name', 'Pending')
                        ->distinct()
                        ->count('customer_orders.purchase_order_id') ?? 0,
                    'total_order_value' => $totalOrderValue,
                    'admin_commission' => $totalOrderValue * $commissionRate,
                    'commission_rate' => $commissionRate * 100,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Stats Error: ' . $e->getMessage());
            $stats = [
                'total_users' => 0,
                'total_customers' => 0,
                'total_business_users' => 0,
                'total_enterprises' => 0,
                'total_services' => 0,
                'total_orders' => 0,
                'pending_orders' => 0,
                'total_revenue' => 0,
            ];
        }

        try {
            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $recent_orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*',
                    'users.name as customer_name',
                    'enterprises.name as enterprise_name',
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total, 0) as total'),
                    DB::raw('COALESCE(customer_orders.order_no, CAST(customer_orders.purchase_order_id AS TEXT)) as order_no')
                )
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Recent Orders Error: ' . $e->getMessage());
            $recent_orders = collect();
        }

        try {
            $recent_users = DB::table('users')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Recent Users Error: ' . $e->getMessage());
            $recent_users = collect();
        }

        // Get data for dashboard tabs
        try {
            $users = DB::table('users')
                ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
                ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->leftJoin('enterprises as owned_enterprises', 'users.user_id', '=', 'owned_enterprises.owner_user_id')
                ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
                ->leftJoin('enterprises as staff_enterprises', 'staff.enterprise_id', '=', 'staff_enterprises.enterprise_id')
                ->select(
                    'users.*', 
                    'role_types.user_role_type as role_type',
                    DB::raw('COALESCE(owned_enterprises.name, staff_enterprises.name) as enterprise_name'),
                    DB::raw('1 as is_active'),
                    DB::raw('COALESCE(users.email, \'\') as email'),
                    DB::raw('COALESCE(login.username, users.name) as username')
                )
                ->orderBy('users.created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Users Tab Error: ' . $e->getMessage());
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        try {
            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*', 
                    'users.name as customer_name', 
                    'enterprises.name as enterprise_name', 
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total, 0) as total'),
                    DB::raw('COALESCE(customer_orders.order_no, CAST(customer_orders.purchase_order_id AS TEXT)) as order_no')
                )
                ->orderBy('customer_orders.created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Orders Tab Error: ' . $e->getMessage());
            $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('admin.dashboard', compact('stats', 'recent_orders', 'recent_users', 'users', 'orders'));
    }

    public function users()
    {
        try {
            $hasUserIsActive = Schema::hasColumn('users', 'is_active');

            $users = DB::table('users')
                ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
                ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->leftJoin('enterprises as owned_enterprises', 'users.user_id', '=', 'owned_enterprises.owner_user_id')
                ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
                ->leftJoin('enterprises as staff_enterprises', 'staff.enterprise_id', '=', 'staff_enterprises.enterprise_id')
                ->select(
                    'users.*', 
                    'role_types.user_role_type as role_type',
                    DB::raw('COALESCE(owned_enterprises.name, staff_enterprises.name) as enterprise_name'),
                    DB::raw(($hasUserIsActive ? 'COALESCE(users.is_active, true)' : 'true') . ' as is_active'),
                    DB::raw('COALESCE(users.email, \'\') as email'),
                    DB::raw('COALESCE(login.username, users.name) as username')
                )
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Users Query Error: ' . $e->getMessage());
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.users', compact('users'));
    }

    public function createUser(Request $request)
    {
        if ($request->ajax()) {
            return view('admin.users.create-form')->render();
        }
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:100|unique:login,username',
            'password' => 'required|string|min:8|confirmed',
            'role_type' => 'required|string',
        ]);

        $desiredRoleType = $request->input('role_type');
        if (!in_array($desiredRoleType, ['customer', 'business_user', 'admin'], true)) {
            $desiredRoleType = 'customer';
        }

        DB::beginTransaction();
        try {
            $userId = (string) Str::uuid();

            $position = $desiredRoleType === 'business_user' ? 'Owner' : ($desiredRoleType === 'admin' ? 'Administrator' : 'Customer');
            $department = $desiredRoleType === 'business_user' ? 'Management' : ($desiredRoleType === 'admin' ? 'Admin' : 'External');

            $userData = [
                'user_id' => $userId,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'position' => $position,
                'department' => $department,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'is_active')) {
                $userData['is_active'] = true;
            }

            DB::table('users')->insert($userData);

            DB::table('login')->insert([
                'login_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roleTypeId = DB::table('role_types')->where('user_role_type', $desiredRoleType)->value('role_type_id');
            if (!$roleTypeId) {
                $roleTypeId = (string) Str::uuid();
                DB::table('role_types')->insert([
                    'role_type_id' => $roleTypeId,
                    'user_role_type' => $desiredRoleType,
                ]);
            }

            DB::table('roles')->insert([
                'role_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'role_type_id' => $roleTypeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->logAudit('create', 'user', $userId, 'Created user', null, [
                'user_id' => $userId,
                'role_type' => $desiredRoleType,
            ]);

            DB::commit();
            return redirect()->route('admin.users')->with('success', 'User created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin create user failed', ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Failed to create user.');
        }
    }

    public function userDetails(Request $request, $id)
    {
        $hasUserIsActive = Schema::hasColumn('users', 'is_active');

        $select = [
            'users.*',
            'role_types.user_role_type as role_type',
            DB::raw('COALESCE(owned_enterprises.enterprise_id, staff_enterprises.enterprise_id) as enterprise_id'),
            DB::raw('COALESCE(owned_enterprises.name, staff_enterprises.name) as enterprise_name'),
            DB::raw(($hasUserIsActive ? 'COALESCE(users.is_active, true)' : 'true') . ' as is_active'),
            DB::raw('COALESCE(users.email, \'\') as email'),
            DB::raw('COALESCE(login.username, users.name) as username'),
        ];

        if (Schema::hasColumn('enterprises', 'is_verified')) {
            $select[] = DB::raw('COALESCE(owned_enterprises.is_verified, staff_enterprises.is_verified) as enterprise_is_verified');
        }
        if (Schema::hasColumn('enterprises', 'verification_document_path')) {
            $select[] = DB::raw('COALESCE(owned_enterprises.verification_document_path, staff_enterprises.verification_document_path) as enterprise_verification_document_path');
        }
        if (Schema::hasColumn('enterprises', 'verification_submitted_at')) {
            $select[] = DB::raw('COALESCE(owned_enterprises.verification_submitted_at, staff_enterprises.verification_submitted_at) as enterprise_verification_submitted_at');
        }

        $user = DB::table('users')
            ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
            ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
            ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->leftJoin('enterprises as owned_enterprises', 'users.user_id', '=', 'owned_enterprises.owner_user_id')
            ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
            ->leftJoin('enterprises as staff_enterprises', 'staff.enterprise_id', '=', 'staff_enterprises.enterprise_id')
            ->where('users.user_id', $id)
            ->select($select)
            ->first();

        if (!$user) {
            abort(404);
        }

        try {
            $orderCount = DB::table('customer_orders')->where('customer_id', $id)->count();
            $orderTotal = DB::table('customer_orders')->where('customer_id', $id)->sum('total') ?? 0;
        } catch (\Exception $e) {
            $orderCount = 0;
            $orderTotal = 0;
        }

        $view = view('admin.users.details', compact('user', 'orderCount', 'orderTotal', 'hasUserIsActive'));

        if ($request->expectsJson() || $request->ajax()) {
            $sections = $view->renderSections();
            return response($sections['content'] ?? '');
        }

        return $view;
    }

    public function verifyEnterprise(Request $request, $id)
    {
        $enterprise = DB::table('enterprises')->where('enterprise_id', $id)->first();
        if (! $enterprise) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enterprise not found.',
                ], 404);
            }

            abort(404);
        }

        if (! Schema::hasColumn('enterprises', 'is_verified')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enterprise verification is not available. Please run migrations.',
                ], 400);
            }

            return redirect()->back()->with('error', 'Enterprise verification is not available. Please run migrations.');
        }

        $update = [
            'is_verified' => true,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('enterprises', 'verified_at')) {
            $update['verified_at'] = now();
        }
        if (Schema::hasColumn('enterprises', 'verified_by_user_id')) {
            $update['verified_by_user_id'] = session('user_id');
        }

        DB::table('enterprises')->where('enterprise_id', $id)->update($update);

        $this->logAudit('update', 'enterprise', $id, 'Verified enterprise', null, $update);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Enterprise verified successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Enterprise verified successfully.');
    }

    public function toggleUserActive($id)
    {
        if (!Schema::hasColumn('users', 'is_active')) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User active status is not supported by the current schema.',
                ], 400);
            }

            return redirect()->back()->with('error', 'User active status is not supported by the current schema.');
        }

        $user = DB::table('users')->where('user_id', $id)->first();
        if (!$user) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'User not found.');
        }

        $newValue = !(bool) ($user->is_active ?? true);

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'is_active' => $newValue,
                'updated_at' => now(),
            ]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $newValue,
                'message' => $newValue ? 'User activated.' : 'User deactivated.',
            ]);
        }

        return redirect()->back()->with('success', $newValue ? 'User activated.' : 'User deactivated.');
    }

    public function disableUserEmail2fa(Request $request, $id)
    {
        if (!Schema::hasColumn('users', 'two_factor_enabled')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email 2FA is not supported by the current schema. Please run migrations.',
                ], 400);
            }

            return redirect()->back()->with('error', 'Email 2FA is not supported by the current schema. Please run migrations.');
        }

        $user = DB::table('users')->where('user_id', $id)->first();
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'User not found.');
        }

        $roleType = (string) (DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $id)
            ->value('role_types.user_role_type') ?? '');
        if ($roleType === 'admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot disable 2FA for admin users.',
                ], 400);
            }

            return redirect()->back()->with('error', 'Cannot disable 2FA for admin users.');
        }

        $currentlyEnabled = (bool) ($user->two_factor_enabled ?? false);
        if (!$currentlyEnabled) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email 2FA is already disabled for this user.',
                    'two_factor_enabled' => false,
                    'updated_rows' => 0,
                ]);
            }

            return redirect()->back()->with('success', 'Email 2FA is already disabled for this user.');
        }

        $oldValues = [
            'two_factor_enabled' => (bool) ($user->two_factor_enabled ?? false),
            'two_factor_code' => (string) ($user->two_factor_code ?? ''),
            'two_factor_expires_at' => $user->two_factor_expires_at ?? null,
        ];

        $update = [
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'two_factor_email_enabled')) {
            $update['two_factor_email_enabled'] = false;
        }
        if (Schema::hasColumn('users', 'two_factor_sms_enabled')) {
            $update['two_factor_sms_enabled'] = false;
        }
        if (Schema::hasColumn('users', 'two_factor_totp_enabled')) {
            $update['two_factor_totp_enabled'] = false;
        }
        if (Schema::hasColumn('users', 'two_factor_enabled_at')) {
            $update['two_factor_enabled_at'] = null;
        }
        if (Schema::hasColumn('users', 'two_factor_secret')) {
            $update['two_factor_secret'] = null;
        }

        try {
            $updatedRows = DB::table('users')->where('user_id', $id)->update($update);
        } catch (\Throwable $e) {
            Log::error('Admin disable email 2FA failed', ['error' => $e->getMessage(), 'user_id' => $id]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to disable Email 2FA for this user.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to disable Email 2FA for this user.');
        }

        $this->logAudit('update', 'user', $id, 'Admin disabled user Email 2FA', $oldValues, array_merge($update, [
            'updated_rows' => $updatedRows ?? null,
        ]));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Email 2FA disabled for this user.',
                'two_factor_enabled' => false,
                'updated_rows' => $updatedRows ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Email 2FA disabled for this user.');
    }

    public function deleteUser(Request $request, string $id)
    {
        $adminId = (string) session('user_id');
        if ($adminId !== '' && hash_equals($adminId, $id)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 400);
            }

            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $roleType = (string) (DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $id)
            ->value('role_types.user_role_type') ?? '');
        if ($roleType === 'admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete admin users.',
                ], 400);
            }

            return redirect()->back()->with('error', 'Cannot delete admin users.');
        }

        $user = DB::table('users')->where('user_id', $id)->first();
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'User not found.');
        }

        $oldValues = [
            'user_id' => (string) ($user->user_id ?? ''),
            'email' => (string) ($user->email ?? ''),
            'name' => (string) ($user->name ?? ''),
            'role_type' => $roleType,
        ];

        try {
            DB::beginTransaction();

            if (Schema::hasTable('roles')) {
                DB::table('roles')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('login')) {
                DB::table('login')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('social_logins')) {
                DB::table('social_logins')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('staff')) {
                DB::table('staff')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('customer_orders')) {
                if (Schema::hasColumn('customer_orders', 'customer_id')) {
                    DB::table('customer_orders')->where('customer_id', $id)->delete();
                }
                if (Schema::hasColumn('customer_orders', 'customer_account_id')) {
                    DB::table('customer_orders')->where('customer_account_id', $id)->delete();
                }
                if (Schema::hasColumn('customer_orders', 'user_id')) {
                    DB::table('customer_orders')->where('user_id', $id)->delete();
                }
            }
            if (Schema::hasTable('saved_services')) {
                DB::table('saved_services')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('design_assets')) {
                DB::table('design_assets')->where('user_id', $id)->delete();
            }
            if (Schema::hasTable('ai_image_generations')) {
                DB::table('ai_image_generations')->where('user_id', $id)->delete();
            }

            $deleted = DB::table('users')->where('user_id', $id)->delete();
            if (!$deleted) {
                DB::rollBack();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to delete user.',
                    ], 500);
                }

                return redirect()->back()->with('error', 'Failed to delete user.');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin delete user failed', [
                'error' => $e->getMessage(),
                'user_id' => $id,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete user.');
        }

        $this->logAudit('delete', 'user', $id, 'Admin deleted user', $oldValues, null);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function enterprises()
    {
        try {
            // Get enterprises with comprehensive data
            $enterprises = DB::table('enterprises')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as services_count FROM services GROUP BY enterprise_id) as service_counts'), 'enterprises.enterprise_id', '=', 'service_counts.enterprise_id')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as orders_count, SUM(COALESCE(total, 0)) as total_revenue FROM customer_orders GROUP BY enterprise_id) as order_stats'), 'enterprises.enterprise_id', '=', 'order_stats.enterprise_id')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as staff_count FROM staff GROUP BY enterprise_id) as staff_counts'), 'enterprises.enterprise_id', '=', 'staff_counts.enterprise_id')
                ->select(
                    'enterprises.*', 
                    DB::raw('COALESCE(service_counts.services_count, 0) as services_count'),
                    DB::raw('COALESCE(order_stats.orders_count, 0) as orders_count'),
                    DB::raw('COALESCE(order_stats.total_revenue, 0) as total_revenue'),
                    DB::raw('COALESCE(staff_counts.staff_count, 0) as staff_count')
                )
                ->orderBy('enterprises.created_at', 'desc')
                ->paginate(20);

            $activeEnterprisesCount = Schema::hasColumn('enterprises', 'is_active')
                ? DB::table('enterprises')->where('is_active', true)->count()
                : DB::table('enterprises')->count();

            $avgServicesPerEnterprise = DB::table(DB::raw('(SELECT enterprises.enterprise_id, COUNT(services.service_id) as service_count FROM enterprises LEFT JOIN services ON enterprises.enterprise_id = services.enterprise_id GROUP BY enterprises.enterprise_id) as subquery'))
                ->avg('service_count') ?? 0;

            // Get enterprise statistics
            $stats = [
                'total_enterprises' => DB::table('enterprises')->count(),
                'active_enterprises' => $activeEnterprisesCount,
                'total_services' => DB::table('services')->count(),
                'total_orders' => DB::table('customer_orders')->count(),
                'total_revenue' => DB::table('customer_orders')->sum('total') ?? 0,
                'avg_services_per_enterprise' => round($avgServicesPerEnterprise, 1)
            ];

            // Get recent activity
            $recent_orders = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->select('customer_orders.*', 'enterprises.name as enterprise_name', 'users.name as customer_name')
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            Log::error('Admin Enterprises Query Error: ' . $e->getMessage());
            $enterprises = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $stats = [
                'total_enterprises' => 0,
                'active_enterprises' => 0,
                'total_services' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'avg_services_per_enterprise' => 0
            ];
            $recent_orders = collect();
        }
        
        return view('admin.enterprises', compact('enterprises', 'stats', 'recent_orders'));
    }

    public function enterpriseDetails(Request $request, $id)
    {
        $enterprise = DB::table('enterprises')->where('enterprise_id', $id)->first();
        if (!$enterprise) {
            abort(404);
        }

        try {
            $services = DB::table('services')
                ->where('services.enterprise_id', $id)
                ->select(
                    'services.*',
                    DB::raw('COALESCE(services.base_price, 0) as base_price'),
                    DB::raw('COALESCE(services.is_active, true) as is_available'),
                    DB::raw('COALESCE(services.description, \'\') as description_text')
                )
                ->orderBy('services.created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            $services = collect();
        }

        try {
            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.enterprise_id', $id)
                ->select(
                    'customer_orders.*',
                    'users.name as customer_name',
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total, 0) as total'),
                    DB::raw('COALESCE(customer_orders.order_no, CAST(customer_orders.purchase_order_id AS TEXT)) as order_no')
                )
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            $orders = collect();
        }

        try {
            $staff = DB::table('staff')
                ->join('users', 'staff.user_id', '=', 'users.user_id')
                ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                ->where('staff.enterprise_id', $id)
                ->select(
                    'staff.*',
                    'users.name',
                    'users.email',
                    DB::raw('COALESCE(login.username, users.name) as username')
                )
                ->orderBy('staff.created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            $staff = collect();
        }

        $stats = [
            'services_count' => DB::table('services')->where('enterprise_id', $id)->count(),
            'orders_count' => DB::table('customer_orders')->where('enterprise_id', $id)->count(),
            'total_revenue' => DB::table('customer_orders')->where('enterprise_id', $id)->sum('total') ?? 0,
            'staff_count' => DB::table('staff')->where('enterprise_id', $id)->count(),
        ];

        $view = view('admin.enterprises.details-partial', compact('enterprise', 'stats', 'services', 'orders', 'staff'));

        if ($request->expectsJson() || $request->ajax()) {
            return $view->render();
        }

        return view('admin.enterprises.details', compact('enterprise', 'stats', 'services', 'orders', 'staff'));
    }

    public function orders(Request $request)
    {
        try {
            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $ordersQuery = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*', 
                    'users.name as customer_name', 
                    'enterprises.name as enterprise_name', 
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total, 0) as total'),
                    DB::raw('COALESCE(customer_orders.order_no, CAST(customer_orders.purchase_order_id AS TEXT)) as order_no')
                )

                ->orderBy('customer_orders.created_at', 'desc');

            if ($request->filled('enterprise_id')) {
                $ordersQuery->where('customer_orders.enterprise_id', $request->input('enterprise_id'));
            }

            $orders = $ordersQuery->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            Log::error('Admin Orders Query Error: ' . $e->getMessage());
            $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.orders', compact('orders'));
    }

    public function orderDetails(Request $request, $id)
    {
        $latestStatusTimes = DB::table('order_status_history')
            ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
            ->groupBy('purchase_order_id');

        $order = DB::table('customer_orders')
            ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
            })
            ->leftJoin('order_status_history as osh', function ($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                    ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
            })
            ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->select(
                'customer_orders.*',
                'users.name as customer_name',
                'users.email as customer_email',
                'enterprises.name as enterprise_name',
                'statuses.status_name',
                DB::raw('COALESCE(customer_orders.total, 0) as total'),
                DB::raw('COALESCE(customer_orders.order_no, CAST(customer_orders.purchase_order_id AS TEXT)) as order_no')
            )
            ->first();

        if (!$order) {
            abort(404);
        }

        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.orders.details-partial', compact('order', 'orderItems'))->render();
        }

        $statusHistory = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->leftJoin('users', 'order_status_history.user_id', '=', 'users.user_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->select('order_status_history.*', 'statuses.status_name', 'statuses.description', 'users.name as user_name')
            ->orderBy('order_status_history.timestamp', 'desc')
            ->get();

        $designFiles = DB::table('order_design_files')
            ->leftJoin('users as uploader', 'order_design_files.uploaded_by', '=', 'uploader.user_id')
            ->leftJoin('users as approver', 'order_design_files.approved_by', '=', 'approver.user_id')
            ->where('order_design_files.purchase_order_id', $id)
            ->select(
                'order_design_files.*',
                'uploader.name as uploaded_by_name',
                'approver.name as approved_by_name'
            )
            ->orderBy('order_design_files.created_at', 'desc')
            ->get();

        $statuses = DB::table('statuses')->get();

        return view('admin.orders.details', compact('order', 'orderItems', 'statusHistory', 'designFiles', 'statuses'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|uuid',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $userId = session('user_id');

        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $id)
            ->first();

        if (!$order) {
            abort(404);
        }

        $oldStatus = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('purchase_order_id', $id)
            ->orderBy('timestamp', 'desc')
            ->select('statuses.status_name')
            ->first();

        $newStatus = DB::table('statuses')
            ->where('status_id', $request->status_id)
            ->first();

        DB::table('order_status_history')->insert([
            'approval_id' => (string) Str::uuid(),
            'purchase_order_id' => $id,
            'user_id' => $userId,
            'status_id' => $request->status_id,
            'remarks' => $request->remarks ?? 'Status updated by admin',
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logAudit(
            'status_change',
            'order',
            $id,
            'Order status updated by admin',
            ['status' => $oldStatus?->status_name ?? null],
            ['status' => $newStatus?->status_name ?? null, 'remarks' => $request->remarks ?? null]
        );

        if (Schema::hasColumn('customer_orders', 'status_id')) {
            DB::table('customer_orders')
                ->where('purchase_order_id', $id)
                ->update([
                    'status_id' => $request->status_id,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('order_notifications')) {
            $oldStatusName = $oldStatus?->status_name ?? 'Unknown';
            $newStatusName = $newStatus?->status_name ?? 'Unknown';

            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $id,
                'recipient_id' => $order->customer_id,
                'message' => "Your order status has been updated from {$oldStatusName} to {$newStatusName}.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.orders.details', $id)->with('success', 'Order status updated successfully');
    }

    public function auditLogs(Request $request)
    {
        $driver = DB::getDriverName();
        $query = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.user_id')
            ->select(
                'audit_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('audit_logs.created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('audit_logs.action', $request->string('action')->toString());
        }

        if ($request->filled('entity_type')) {
            $query->where('audit_logs.entity_type', $request->string('entity_type')->toString());
        }

        if ($request->filled('user_id')) {
            $query->where('audit_logs.user_id', $request->string('user_id')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($sub) use ($q, $op) {
                $sub->where('audit_logs.description', $op, "%{$q}%")
                    ->orWhere('audit_logs.action', $op, "%{$q}%")
                    ->orWhere('audit_logs.entity_type', $op, "%{$q}%")
                    ->orWhere('audit_logs.ip_address', $op, "%{$q}%")
                    ->orWhere('users.name', $op, "%{$q}%")
                    ->orWhere('users.email', $op, "%{$q}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }

    public function services(Request $request)
    {
        try {
            $servicesQuery = DB::table('services')
                ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
                ->select(
                    'services.*', 
                    'enterprises.name as enterprise_name',
                    DB::raw('COALESCE(services.base_price, 0) as base_price'),
                    DB::raw('COALESCE(services.is_active, true) as is_available'),
                    DB::raw('COALESCE(services.description, \'\') as description_text')
                );

            if ($request->filled('enterprise_id')) {
                $servicesQuery->where('services.enterprise_id', $request->input('enterprise_id'));
            }

            $services = $servicesQuery->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            Log::error('Admin Services Query Error: ' . $e->getMessage());
            $services = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.services', compact('services'));
    }

    public function serviceDetails(Request $request, $id)
    {
        $service = DB::table('services')
            ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('services.service_id', $id)
            ->select(
                'services.*',
                'enterprises.name as enterprise_name',
                DB::raw('COALESCE(services.base_price, 0) as base_price'),
                DB::raw('COALESCE(services.is_active, true) as is_available'),
                DB::raw('COALESCE(services.description, \'\') as description_text')
            )
            ->first();

        if (!$service) {
            abort(404);
        }

        $orderCount = DB::table('order_items')
            ->where('service_id', $id)
            ->count();

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.services.details-partial', compact('service', 'orderCount'))->render();
        }

        return view('admin.services.details', compact('service', 'orderCount'));
    }

    public function toggleServiceActive($id)
    {
        if (!Schema::hasColumn('services', 'is_active')) {
            return redirect()->back()->with('error', 'Service active status is not supported by the current schema.');
        }

        $service = DB::table('services')->where('service_id', $id)->first();
        if (!$service) {
            return redirect()->back()->with('error', 'Service not found.');
        }

        $newValue = !(bool) ($service->is_active ?? true);

        DB::table('services')
            ->where('service_id', $id)
            ->update([
                'is_active' => $newValue,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', $newValue ? 'Service activated.' : 'Service deactivated.');
    }

    public function toggleEnterpriseActive($id)
    {
        if (!Schema::hasColumn('enterprises', 'is_active')) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enterprise active status is not supported by the current schema.',
                ], 400);
            }

            return redirect()->back()->with('error', 'Enterprise active status is not supported by the current schema.');
        }

        $enterprise = DB::table('enterprises')->where('enterprise_id', $id)->first();
        if (!$enterprise) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enterprise not found.',
                ], 404);
            }

            return redirect()->back()->with('error', 'Enterprise not found.');
        }

        $newValue = !(bool) ($enterprise->is_active ?? true);

        DB::table('enterprises')
            ->where('enterprise_id', $id)
            ->update([
                'is_active' => $newValue,
                'updated_at' => now(),
            ]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $newValue,
                'message' => $newValue ? 'Enterprise activated.' : 'Enterprise deactivated.',
            ]);
        }

        return redirect()->back()->with('success', $newValue ? 'Enterprise activated.' : 'Enterprise deactivated.');
    }

    public function reports()
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $monthExpr = "DATE_FORMAT(transaction_date, '%Y-%m-01')";
            } elseif ($driver === 'sqlite') {
                $monthExpr = "strftime('%Y-%m-01', transaction_date)";
            } else {
                $monthExpr = "DATE_TRUNC('month', transaction_date)";
            }

            $revenue_by_month = DB::table('transactions')
                ->select(DB::raw("{$monthExpr} as month"), DB::raw('SUM(amount) as revenue'))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();
        } catch (\Exception $e) {
            Log::error('Admin Reports Revenue Query Error: ' . $e->getMessage());
            $revenue_by_month = collect();
        }

        try {
            $orders_by_status = DB::table('order_status_history')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->select('statuses.status_name', DB::raw('count(DISTINCT order_status_history.purchase_order_id) as count'))
                ->groupBy('statuses.status_name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Admin Reports Orders by Status Query Error: ' . $e->getMessage());
            $orders_by_status = collect();
        }

        try {
            $top_enterprises = DB::table('enterprises')
                ->leftJoin('customer_orders', 'enterprises.enterprise_id', '=', 'customer_orders.enterprise_id')
                ->select('enterprises.name', DB::raw('count(customer_orders.purchase_order_id) as orders_count'))
                ->groupBy('enterprises.enterprise_id', 'enterprises.name')
                ->orderBy('orders_count', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::error('Admin Reports Top Enterprises Query Error: ' . $e->getMessage());
            $top_enterprises = collect();
        }

        return view('admin.reports', compact('revenue_by_month', 'orders_by_status', 'top_enterprises'));
    }

    public function userReports(Request $request)
    {
        if (! Schema::hasTable('user_reports')) {
            $reports = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            return view('admin.user-reports', compact('reports'));
        }

        $query = DB::table('user_reports as ur')
            ->leftJoin('users as u', 'ur.reporter_id', '=', 'u.user_id')
            ->leftJoin('enterprises as e', 'ur.enterprise_id', '=', 'e.enterprise_id')
            ->leftJoin('services as s', 'ur.service_id', '=', 's.service_id')
            ->select(
                'ur.*',
                'u.name as reporter_name',
                'e.name as enterprise_name',
                's.service_name as service_name'
            )
            ->orderBy('ur.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('ur.status', $request->string('status')->toString());
        }

        $reports = $query->paginate(25)->withQueryString();

        return view('admin.user-reports', compact('reports'));
    }

    public function resolveUserReport(Request $request, string $id)
    {
        if (! Schema::hasTable('user_reports')) {
            return redirect()->back()->with('error', 'Reports table is not available.');
        }

        $updated = DB::table('user_reports')
            ->where('report_id', $id)
            ->update([
                'status' => 'resolved',
                'resolved_by' => session('user_id'),
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return redirect()->back()->with('error', 'Unable to resolve report.');
        }

        $this->logAudit('update', 'user_report', $id, 'Resolved user report');

        return redirect()->back()->with('success', 'Report resolved.');
    }

    /**
     * Get real-time dashboard statistics via AJAX
     */
    public function getDashboardStats()
    {
        try {
            // Commission rate (should match the one in dashboard method)
            $commissionRate = 0.05; // 5% commission rate

            $latestStatusTimes = DB::table('order_status_history')
                ->select('purchase_order_id', DB::raw('MAX(timestamp) as latest_time'))
                ->groupBy('purchase_order_id');

            $ordersWithLatestStatus = DB::table('customer_orders')
                ->leftJoinSub($latestStatusTimes, 'latest_status_times', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'latest_status_times.purchase_order_id');
                })
                ->leftJoin('order_status_history as osh', function ($join) {
                    $join->on('customer_orders.purchase_order_id', '=', 'osh.purchase_order_id')
                        ->on('osh.timestamp', '=', 'latest_status_times.latest_time');
                })
                ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id');

            $totalOrderValue = DB::table('customer_orders')->sum('total') ?? 0;
            
            $stats = [
                'total_users' => DB::table('users')->count() ?? 0,
                'total_customers' => DB::table('roles')
                    ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                    ->where('role_types.user_role_type', 'customer')
                    ->distinct()
                    ->count('roles.user_id') ?? 0,
                'total_business_users' => DB::table('roles')
                    ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                    ->where('role_types.user_role_type', 'business_user')
                    ->distinct()
                    ->count('roles.user_id') ?? 0,
                'total_enterprises' => DB::table('enterprises')->count() ?? 0,
                'total_services' => DB::table('services')->count() ?? 0,
                'total_orders' => DB::table('customer_orders')->count() ?? 0,
                'pending_orders' => (clone $ordersWithLatestStatus)
                    ->where('statuses.status_name', 'Pending')
                    ->distinct()
                    ->count('customer_orders.purchase_order_id') ?? 0,
                'total_order_value' => $totalOrderValue,
                'admin_commission' => $totalOrderValue * $commissionRate,
                'commission_rate' => $commissionRate * 100,
                'last_updated' => now()->format('H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Dashboard Stats API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics'
            ], 500);
        }
    }

    /**
     * Get real-time enterprise statistics via AJAX
     */
    public function getEnterpriseStats()
    {
        try {
            $activeEnterprisesCount = Schema::hasColumn('enterprises', 'is_active')
                ? DB::table('enterprises')->where('is_active', true)->count()
                : DB::table('enterprises')->count();

            $stats = [
                'total_enterprises' => DB::table('enterprises')->count(),
                'active_enterprises' => $activeEnterprisesCount,
                'total_services' => DB::table('services')->count(),
                'total_orders' => DB::table('customer_orders')->count(),
                'total_revenue' => DB::table('customer_orders')->sum('total') ?? 0,
                'avg_services_per_enterprise' => round(DB::table('services')->count() / max(DB::table('enterprises')->count(), 1), 1),
                'last_updated' => now()->format('H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Enterprise Stats API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch enterprise statistics'
            ], 500);
        }
    }
}
