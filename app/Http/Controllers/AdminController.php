<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\SafePropertyAccess;

class AdminController extends Controller
{
    use SafePropertyAccess;
    public function dashboard()
    {
        try {
            // Commission rate (configurable - could be moved to settings)
            $commissionRate = 0.05; // 5% commission rate
            
            $stats = [
                'total_users' => DB::table('users')->count() ?? 0,
                'total_customers' => DB::table('users')->where('role_type', 'customer')->count() ?? 0,
                'total_business_users' => DB::table('users')->where('role_type', 'business_user')->count() ?? 0,
                'total_enterprises' => DB::table('enterprises')->count() ?? 0,
                'total_services' => DB::table('services')->count() ?? 0,
                'total_orders' => DB::table('customer_orders')->count() ?? 0,
                'pending_orders' => DB::table('customer_orders')
                    ->leftJoin('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                    ->leftJoin('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                    ->where('statuses.status_name', 'Pending')
                    ->distinct()
                    ->count('customer_orders.purchase_order_id') ?? 0,
                'total_order_value' => DB::table('customer_orders')->sum('total_order_amount') ?? 0,
                'admin_commission' => (DB::table('customer_orders')->sum('total_order_amount') ?? 0) * $commissionRate,
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
            $recent_orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id ORDER BY latest_time DESC) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
                ->select('customer_orders.*', 'users.name as customer_name', 'enterprises.name as enterprise_name', 'statuses.status_name')
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
                ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
                ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
                ->leftJoin('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
                ->select(
                    'users.*', 
                    'role_types.user_role_type as role_type',
                    'enterprises.name as enterprise_name',
                    DB::raw('CASE WHEN users.is_active IS NULL THEN true ELSE users.is_active END as is_active'),
                    DB::raw('CASE WHEN users.email IS NULL THEN \'\' ELSE users.email END as email'),
                    DB::raw('CASE WHEN users.username IS NULL THEN users.name ELSE users.username END as username')
                )
                ->orderBy('users.created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Users Tab Error: ' . $e->getMessage());
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        try {
            $orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id ORDER BY latest_time DESC) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*', 
                    'users.name as customer_name', 
                    'enterprises.name as enterprise_name', 
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total_order_amount, 0) as total'),
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
            $users = DB::table('users')
                ->leftJoin('roles', 'users.user_id', '=', 'roles.user_id')
                ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->leftJoin('staff', 'users.user_id', '=', 'staff.user_id')
                ->leftJoin('enterprises', 'staff.enterprise_id', '=', 'enterprises.enterprise_id')
                ->select(
                    'users.*', 
                    'role_types.user_role_type as role_type',
                    'enterprises.name as enterprise_name',
                    DB::raw('CASE WHEN users.is_active IS NULL THEN true ELSE users.is_active END as is_active'),
                    DB::raw('CASE WHEN users.email IS NULL THEN \'\' ELSE users.email END as email'),
                    DB::raw('CASE WHEN users.username IS NULL THEN users.name ELSE users.username END as username')
                )
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Users Query Error: ' . $e->getMessage());
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.users', compact('users'));
    }

    public function enterprises()
    {
        try {
            // Get enterprises with comprehensive data
            $enterprises = DB::table('enterprises')
                ->leftJoin('vat_types', 'enterprises.vat_type_id', '=', 'vat_types.vat_type_id')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as services_count FROM services GROUP BY enterprise_id) as service_counts'), 'enterprises.enterprise_id', '=', 'service_counts.enterprise_id')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as orders_count, SUM(COALESCE(total_order_amount, 0)) as total_revenue FROM customer_orders GROUP BY enterprise_id) as order_stats'), 'enterprises.enterprise_id', '=', 'order_stats.enterprise_id')
                ->leftJoin(DB::raw('(SELECT enterprise_id, COUNT(*) as staff_count FROM staff GROUP BY enterprise_id) as staff_counts'), 'enterprises.enterprise_id', '=', 'staff_counts.enterprise_id')
                ->select(
                    'enterprises.*', 
                    'vat_types.type_name as vat_type',
                    DB::raw('COALESCE(service_counts.services_count, 0) as services_count'),
                    DB::raw('COALESCE(order_stats.orders_count, 0) as orders_count'),
                    DB::raw('COALESCE(order_stats.total_revenue, 0) as total_revenue'),
                    DB::raw('COALESCE(staff_counts.staff_count, 0) as staff_count')
                )
                ->orderBy('enterprises.created_at', 'desc')
                ->paginate(20);

            // Get enterprise statistics
            $stats = [
                'total_enterprises' => DB::table('enterprises')->count(),
                'active_enterprises' => DB::table('enterprises')->count(), // All are active for now
                'total_services' => DB::table('services')->count(),
                'total_orders' => DB::table('customer_orders')->count(),
                'total_revenue' => DB::table('customer_orders')->sum('total_order_amount') ?? 0,
                'avg_services_per_enterprise' => DB::table('enterprises')
                    ->leftJoin('services', 'enterprises.enterprise_id', '=', 'services.enterprise_id')
                    ->selectRaw('AVG(COALESCE(service_count, 0)) as avg_services')
                    ->from(DB::raw('(SELECT enterprises.enterprise_id, COUNT(services.service_id) as service_count FROM enterprises LEFT JOIN services ON enterprises.enterprise_id = services.enterprise_id GROUP BY enterprises.enterprise_id) as subquery'))
                    ->value('avg_services') ?? 0
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

    public function orders()
    {
        try {
            $orders = DB::table('customer_orders')
                ->join('users', 'customer_orders.customer_id', '=', 'users.user_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id ORDER BY latest_time DESC) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*', 
                    'users.name as customer_name', 
                    'enterprises.name as enterprise_name', 
                    'statuses.status_name',
                    DB::raw('COALESCE(customer_orders.total_order_amount, 0) as total'),
                    DB::raw('COALESCE(customer_orders.order_no, customer_orders.purchase_order_id) as order_no')
                )
                ->orderBy('customer_orders.created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Orders Query Error: ' . $e->getMessage());
            $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.orders', compact('orders'));
    }

    public function services()
    {
        try {
            $services = DB::table('services')
                ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
                ->select(
                    'services.*', 
                    'enterprises.name as enterprise_name',
                    DB::raw('COALESCE(services.base_price, 0) as base_price'),
                    DB::raw('COALESCE(services.is_available, true) as is_available'),
                    DB::raw('COALESCE(services.description_text, \'\') as description_text')
                )
                ->paginate(20);
        } catch (\Exception $e) {
            Log::error('Admin Services Query Error: ' . $e->getMessage());
            $services = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        
        return view('admin.services', compact('services'));
    }

    // Backward compatibility
    public function products()
    {
        return $this->services();
    }

    public function reports()
    {
        try {
            $revenue_by_month = DB::table('transactions')
                ->select(
                    DB::raw('DATE_TRUNC(\'month\', transaction_date) as month'),
                    DB::raw('SUM(amount) as revenue')
                )
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
            
            $totalOrderValue = DB::table('customer_orders')->sum('total_order_amount') ?? 0;
            
            $stats = [
                'total_users' => DB::table('users')->count() ?? 0,
                'total_customers' => DB::table('users')->where('role_type', 'customer')->count() ?? 0,
                'total_business_users' => DB::table('users')->where('role_type', 'business_user')->count() ?? 0,
                'total_enterprises' => DB::table('enterprises')->count() ?? 0,
                'total_services' => DB::table('services')->count() ?? 0,
                'total_orders' => DB::table('customer_orders')->count() ?? 0,
                'pending_orders' => DB::table('customer_orders')
                    ->leftJoin('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                    ->leftJoin('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
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
            $stats = [
                'total_enterprises' => DB::table('enterprises')->count(),
                'active_enterprises' => DB::table('enterprises')->count(),
                'total_services' => DB::table('services')->count(),
                'total_orders' => DB::table('customer_orders')->count(),
                'total_revenue' => DB::table('customer_orders')->sum('total_order_amount') ?? 0,
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
