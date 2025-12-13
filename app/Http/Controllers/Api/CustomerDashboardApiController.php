<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SavedService;
use App\Models\Service;
use App\Models\Enterprise;

class CustomerDashboardApiController extends Controller
{
    /**
     * Get services catalog data
     */
    public function getServices(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = Service::with(['enterprise'])
                ->where('is_active', true)
                ->select('service_id', 'service_name', 'description', 'base_price', 'enterprise_id');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('service_name', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'ILIKE', "%{$searchTerm}%");
                });
            }

            // Category filter
            if ($request->has('category') && !empty($request->category)) {
                $categoryMap = [
                    'business-cards' => 'Business Card',
                    'flyers' => 'Flyer',
                    'posters' => 'Poster',
                    't-shirts' => 'T-Shirt',
                    'documents' => 'Document'
                ];
                
                if (isset($categoryMap[$request->category])) {
                    $query->where('service_name', 'ILIKE', "%{$categoryMap[$request->category]}%");
                }
            }

            // Price range filter
            if ($request->has('price_range') && !empty($request->price_range)) {
                switch($request->price_range) {
                    case '0-100':
                        $query->where('base_price', '<', 100);
                        break;
                    case '100-500':
                        $query->whereBetween('base_price', [100, 500]);
                        break;
                    case '500-1000':
                        $query->whereBetween('base_price', [500, 1000]);
                        break;
                    case '1000+':
                        $query->where('base_price', '>', 1000);
                        break;
                }
            }

            // Sort functionality
            switch($request->get('sort', 'popular')) {
                case 'price-low':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price-high':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                default:
                    $query->orderBy('base_price', 'asc'); // Default sort
                    break;
            }

            $services = $query->paginate(12);

            Log::info('Services catalog loaded', [
                'user_id' => $userId,
                'count' => $services->count(),
                'filters' => $request->only(['search', 'category', 'price_range', 'sort'])
            ]);

            return response()->json($services->items());

        } catch (\Exception $e) {
            Log::error('Failed to load services catalog', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to load services'], 500);
        }
    }

    /**
     * Get customer orders
     */
    public function getOrders(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $orders = DB::table('customer_orders')
                ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(10)
                ->select(
                    'customer_orders.purchase_order_id',
                    'customer_orders.total',
                    'customer_orders.created_at',
                    'statuses.status_name',
                    'enterprises.name as enterprise_name'
                )
                ->get();

            Log::info('Customer orders loaded', [
                'user_id' => $userId,
                'count' => $orders->count()
            ]);

            return response()->json($orders);

        } catch (\Exception $e) {
            Log::error('Failed to load customer orders', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load orders'], 500);
        }
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Mock payment history data - in real implementation, this would come from payments table
            $payments = DB::table('customer_orders')
                ->where('customer_id', $userId)
                ->whereIn('status_id', function($query) {
                    $query->select('status_id')
                        ->from('statuses')
                        ->whereIn('status_name', ['Delivered', 'Ready for Pickup']);
                })
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->select(
                    'purchase_order_id as description',
                    'total as amount',
                    'created_at as date',
                    DB::raw("'Credit Card' as method")
                )
                ->get()
                ->map(function($payment) {
                    return [
                        'description' => 'Order #' . substr($payment->description, 0, 8),
                        'amount' => number_format($payment->amount, 2),
                        'date' => date('M d, Y', strtotime($payment->date)),
                        'method' => $payment->method
                    ];
                });

            Log::info('Payment history loaded', [
                'user_id' => $userId,
                'count' => $payments->count()
            ]);

            return response()->json($payments);

        } catch (\Exception $e) {
            Log::error('Failed to load payment history', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load payment history'], 500);
        }
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:1000'
            ]);

            $updated = DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Customer profile updated', [
                    'user_id' => $userId,
                    'email' => $validated['email']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes made to profile'
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update customer profile', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get order statistics
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


            // Get saved services count
            $savedServicesCount = SavedService::getServicesCount($userId);

            // Get recent activity
            $recentActivity = DB::table('customer_orders')
                ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(5)
                ->select(
                    'customer_orders.purchase_order_id',
                    'customer_orders.total',
                    'customer_orders.created_at',
                    'statuses.status_name',
                    'enterprises.name as enterprise_name'
                )
                ->get();

            Log::info('Dashboard statistics loaded', [
                'user_id' => $userId,
                'order_stats' => $orderStats,
                'saved_services_count' => $savedServicesCount
            ]);

            return response()->json([
                'order_stats' => $orderStats,
                'saved_services_count' => $savedServicesCount,
                'recent_activity' => $recentActivity
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load dashboard statistics', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load dashboard data'], 500);
        }
    }

    /**
     * Get saved services
     */
    public function getSavedServices(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $savedServices = SavedService::with(['service.enterprise'])
                ->where('user_id', $userId)
                ->orderBy('saved_at', 'desc')
                ->get()
                ->map(function($savedService) {
                    return [
                        'saved_service_id' => $savedService->saved_service_id,
                        'service_name' => $savedService->service->service_name ?? 'Unknown Service',
                        'enterprise_name' => $savedService->service->enterprise->name ?? 'Unknown Shop',
                        'quantity' => $savedService->quantity,
                        'unit_price' => $savedService->formatted_unit_price,
                        'total_price' => $savedService->formatted_total_price,
                        'saved_at' => $savedService->saved_at->format('M d, Y'),
                        'customizations' => $savedService->customizationOptions->pluck('option_name')->join(', ')
                    ];
                });

            Log::info('Saved services loaded', [
                'user_id' => $userId,
                'count' => $savedServices->count()
            ]);

            return response()->json($savedServices);

        } catch (\Exception $e) {
            Log::error('Failed to load saved services', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load saved services'], 500);
        }
    }
}
