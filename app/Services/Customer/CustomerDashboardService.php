<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Customer Dashboard Service
 * Handles dashboard statistics and analytics for customer users
 */
class CustomerDashboardService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get comprehensive dashboard statistics for a customer
     *
     * @param string $userId
     * @param bool $useCache
     * @return array
     */
    public function getDashboardStats(string $userId, bool $useCache = true): array
    {
        try {
            $cacheKey = "customer_dashboard_stats_{$userId}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $stats = [
                'orders' => $this->getOrderStats($userId),
                'assets' => $this->getAssetStats($userId),
                'financial' => $this->getFinancialStats($userId),
                'activity' => $this->getActivityStats($userId),
                'recent_orders' => $this->getRecentOrders($userId, 5),
            ];

            if ($useCache) {
                Cache::put($cacheKey, $stats, self::CACHE_TTL);
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Error getting dashboard stats', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get order statistics
     *
     * @param string $userId
     * @return array
     */
    private function getOrderStats(string $userId): array
    {
        $totalOrders = DB::table('customer_orders')
            ->where('customer_id', $userId)
            ->count();

        // Get status counts
        $statusCounts = DB::table('customer_orders')
            ->join(DB::raw('(
                SELECT purchase_order_id, status_id, 
                       ROW_NUMBER() OVER (PARTITION BY purchase_order_id ORDER BY timestamp DESC) as rn
                FROM order_status_history
            ) as latest_status'), function($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                     ->where('latest_status.rn', '=', 1);
            })
            ->join('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.customer_id', $userId)
            ->select('statuses.status_name', DB::raw('COUNT(*) as count'))
            ->groupBy('statuses.status_name')
            ->pluck('count', 'status_name')
            ->toArray();

        return [
            'total' => $totalOrders,
            'pending' => $statusCounts['Pending'] ?? 0,
            'in_progress' => $statusCounts['In Progress'] ?? 0,
            'completed' => $statusCounts['Delivered'] ?? 0,
            'cancelled' => $statusCounts['Cancelled'] ?? 0,
            'status_breakdown' => $statusCounts,
        ];
    }

    /**
     * Get asset statistics (designs, files)
     *
     * @param string $userId
     * @return array
     */
    private function getAssetStats(string $userId): array
    {
        $userDesigns = DB::table('user_designs')
            ->where('user_id', $userId)
            ->where('is_deleted', false)
            ->count();

        $uploadedFiles = DB::table('order_design_files')
            ->where('uploaded_by', $userId)
            ->count();

        $approvedFiles = DB::table('order_design_files')
            ->where('uploaded_by', $userId)
            ->where('is_approved', true)
            ->count();

        return [
            'user_designs' => $userDesigns,
            'uploaded_files' => $uploadedFiles,
            'approved_files' => $approvedFiles,
            'total_assets' => $userDesigns + $uploadedFiles,
        ];
    }

    /**
     * Get financial statistics
     *
     * @param string $userId
     * @return array
     */
    private function getFinancialStats(string $userId): array
    {
        $totals = DB::table('customer_orders')
            ->where('customer_id', $userId)
            ->selectRaw('
                SUM(total) as total_spent,
                SUM(subtotal) as subtotal,
                SUM(shipping_fee) as shipping_fees,
                SUM(discount) as discounts,
                AVG(total) as average_order_value
            ')
            ->first();

        $lastMonthSpent = DB::table('customer_orders')
            ->where('customer_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->sum('total');

        return [
            'total_spent' => round($totals->total_spent ?? 0, 2),
            'subtotal' => round($totals->subtotal ?? 0, 2),
            'shipping_fees' => round($totals->shipping_fees ?? 0, 2),
            'discounts' => round($totals->discounts ?? 0, 2),
            'average_order_value' => round($totals->average_order_value ?? 0, 2),
            'last_month_spent' => round($lastMonthSpent, 2),
        ];
    }

    /**
     * Get activity statistics
     *
     * @param string $userId
     * @return array
     */
    private function getActivityStats(string $userId): array
    {
        $lastLogin = DB::table('login')
            ->where('user_id', $userId)
            ->value('last_login');

        $recentActivity = DB::table('order_status_history')
            ->join('customer_orders', 'order_status_history.purchase_order_id', '=', 'customer_orders.purchase_order_id')
            ->where('customer_orders.customer_id', $userId)
            ->where('order_status_history.timestamp', '>=', Carbon::now()->subDays(7))
            ->count();

        $unreadNotifications = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->where('is_read', false)
            ->count();

        return [
            'last_login' => $lastLogin,
            'recent_activity_count' => $recentActivity,
            'unread_notifications' => $unreadNotifications,
        ];
    }

    /**
     * Get recent orders
     *
     * @param string $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getRecentOrders(string $userId, int $limit = 5)
    {
        return DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->leftJoin(DB::raw('(
                SELECT purchase_order_id, status_id,
                       ROW_NUMBER() OVER (PARTITION BY purchase_order_id ORDER BY timestamp DESC) as rn
                FROM order_status_history
            ) as latest_status'), function($join) {
                $join->on('customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
                     ->where('latest_status.rn', '=', 1);
            })
            ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.customer_id', $userId)
            ->select(
                'customer_orders.purchase_order_id',
                'customer_orders.order_no',
                'customer_orders.total',
                'customer_orders.created_at',
                'enterprises.name as enterprise_name',
                'statuses.status_name'
            )
            ->orderBy('customer_orders.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear dashboard cache for a user
     *
     * @param string $userId
     * @return void
     */
    public function clearCache(string $userId): void
    {
        Cache::forget("customer_dashboard_stats_{$userId}");
    }
}
