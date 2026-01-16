<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Traits\SafePropertyAccess;

class AdminController extends Controller
{
    use SafePropertyAccess;
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
                'commission_rate' => $commissionRate * 100, // Store as percentage for display
            ];
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
                    DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
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
                    DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
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

    public function userDetails($id)
    {
        $hasUserIsActive = Schema::hasColumn('users', 'is_active');

        $user = DB::table('users')
            ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
            ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
            ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->leftJoin('enterprises as owned_enterprises', 'users.user_id', '=', 'owned_enterprises.owner_user_id')
            ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
            ->leftJoin('enterprises as staff_enterprises', 'staff.enterprise_id', '=', 'staff_enterprises.enterprise_id')
            ->where('users.user_id', $id)
            ->select(
                'users.*',
                'role_types.user_role_type as role_type',
                DB::raw('COALESCE(owned_enterprises.enterprise_id, staff_enterprises.enterprise_id) as enterprise_id'),
                DB::raw('COALESCE(owned_enterprises.name, staff_enterprises.name) as enterprise_name'),
                DB::raw(($hasUserIsActive ? 'COALESCE(users.is_active, true)' : 'true') . ' as is_active'),
                DB::raw('COALESCE(users.email, \'\') as email'),
                DB::raw('COALESCE(login.username, users.name) as username')
            )
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

        return view('admin.users.details', compact('user', 'orderCount', 'orderTotal', 'hasUserIsActive'));
    }

    public function toggleUserActive($id)
    {
        if (!Schema::hasColumn('users', 'is_active')) {
            return redirect()->back()->with('error', 'User active status is not supported by the current schema.');
        }

        $user = DB::table('users')->where('user_id', $id)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $newValue = !(bool) ($user->is_active ?? true);

        DB::table('users')
            ->where('user_id', $id)
            ->update([
                'is_active' => $newValue,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', $newValue ? 'User activated.' : 'User deactivated.');
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
                    DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
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
                    DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
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

    public function orderDetails($id)
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
                DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
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

        foreach ($orderItems as $item) {
            $item->customizations = DB::table('order_item_customizations')
                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                ->where('order_item_customizations.order_item_id', $item->item_id)
                ->select('customization_options.*', 'order_item_customizations.price_snapshot')
                ->get();
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

        if (Schema::hasColumn('customer_orders', 'status_id')) {
            DB::table('customer_orders')
                ->where('purchase_order_id', $id)
                ->update([
                    'status_id' => $request->status_id,
                    'updated_at' => now(),
                ]);
        }

        $newStatus = DB::table('statuses')
            ->where('status_id', $request->status_id)
            ->first();

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

    public function serviceDetails($id)
    {
        $service = DB::table('services')
            ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('services.service_id', $id)
            ->select(
                'services.*',
                'enterprises.name as enterprise_name',
                'enterprises.enterprise_id as enterprise_id',
                DB::raw('COALESCE(services.base_price, 0) as base_price'),
                DB::raw('COALESCE(services.is_active, true) as is_available'),
                DB::raw('COALESCE(services.description, \'\') as description_text')
            )
            ->first();

        if (!$service) {
            abort(404);
        }

        try {
            $orderCount = DB::table('order_items')
                ->join('customer_orders', 'order_items.purchase_order_id', '=', 'customer_orders.purchase_order_id')
                ->where('order_items.service_id', $id)
                ->distinct()
                ->count('customer_orders.purchase_order_id');
        } catch (\Exception $e) {
            $orderCount = 0;
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
            return redirect()->back()->with('error', 'Enterprise active status is not supported by the current schema.');
        }

        $enterprise = DB::table('enterprises')->where('enterprise_id', $id)->first();
        if (!$enterprise) {
            return redirect()->back()->with('error', 'Enterprise not found.');
        }

        $newValue = !(bool) ($enterprise->is_active ?? true);

        DB::table('enterprises')
            ->where('enterprise_id', $id)
            ->update([
                'is_active' => $newValue,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', $newValue ? 'Enterprise activated.' : 'Enterprise deactivated.');
    }

    // Backward compatibility
    public function products()
    {
        return $this->services();
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
