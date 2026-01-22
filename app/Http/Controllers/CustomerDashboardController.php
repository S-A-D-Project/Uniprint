<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SavedService;
use App\Models\CustomerOrder;

class CustomerDashboardController extends Controller
{
    /**
     * Display the customer dashboard
     */
    public function index()
    {
        $userId = session('user_id');
        
        Log::info('Customer dashboard accessed', ['user_id' => $userId]);
        
        if (!$userId) {
            Log::warning('Unauthorized dashboard access attempt - no user ID in session');
            return redirect()->route('login');
        }
        
        try {
            // Get user information
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                Log::warning('User not found in database', ['user_id' => $userId]);
                return redirect()->route('login');
            }
            
            Log::info('Loading dashboard data', ['user_id' => $userId]);
            
            // Get recent orders (limit to 5 most recent)
            $recentOrders = DB::table('customer_orders')
                ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(5)
                ->select(
                    'customer_orders.*',
                    'statuses.status_name',
                    'statuses.description as status_description',
                    'enterprises.name as enterprise_name',
                    'enterprises.shop_logo'
                )
                ->get();
            
            Log::info('Recent orders loaded', ['user_id' => $userId, 'count' => $recentOrders->count()]);
            
            // Get order counts by status
            $orderStats = [
                'pending' => DB::table('customer_orders')
                    ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                    ->where('customer_orders.customer_id', $userId)
                    ->where('statuses.status_name', 'Pending')
                    ->count(),
                'in_progress' => DB::table('customer_orders')
                    ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                    ->where('customer_orders.customer_id', $userId)
                    ->where('statuses.status_name', 'In Progress')
                    ->count(),
                'completed' => DB::table('customer_orders')
                    ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                    ->where('customer_orders.customer_id', $userId)
                    ->whereIn('statuses.status_name', ['Delivered', 'Ready for Pickup'])
                    ->count(),
            ];
            
            Log::info('Order stats calculated', ['user_id' => $userId, 'stats' => $orderStats]);
            
            // Get saved services
            $savedServices = SavedService::getUserServices($userId);
            
            Log::info('Saved services loaded', ['user_id' => $userId, 'count' => $savedServices->count()]);
            
            return view('customer.dashboard', compact(
                'user',
                'recentOrders',
                'orderStats',
                'savedServices'
            ));
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database query error in customer dashboard', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);
            
            return back()->with('error', 'Unable to load dashboard data. Please try again later.');
            
        } catch (\Exception $e) {
            Log::error('Unexpected error in customer dashboard', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }
    
    /**
     * Get user orders
     */
    public function orders(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $status = $request->get('status', 'all');
        
        $query = DB::table('customer_orders')
            ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.customer_id', $userId)
            ->select(
                'customer_orders.*',
                'statuses.status_name',
                'statuses.description as status_description',
                'enterprises.name as enterprise_name',
                'enterprises.shop_logo'
            );
        
        if ($status !== 'all') {
            $query->where('statuses.status_name', $status);
        }
        
        $orders = $query->orderBy('customer_orders.created_at', 'desc')->paginate(10);
        
        return view('customer.orders', compact('orders', 'status'));
    }
    
    /**
     * Get saved services
     */
    public function savedServices()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $savedServices = SavedService::getUserServices($userId);
        
        return view('customer.saved-services', compact('savedServices'));
    }
}
