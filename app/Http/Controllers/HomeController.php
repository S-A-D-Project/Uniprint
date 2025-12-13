<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Home Controller
 * 
 * Handles public-facing landing page and enterprise discovery
 * 
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Display the landing page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get basic statistics
        $stats = [
            'total_enterprises' => DB::table('enterprises')->count(),
            'total_services' => DB::table('services')->count(),
            'categories' => DB::table('services')->distinct()->count('category'),
        ];

        // Get featured enterprises (align with real columns: name, address)
        $enterprises = DB::table('enterprises')
            ->leftJoin('services', 'enterprises.enterprise_id', '=', 'services.enterprise_id')
            ->select(
                'enterprises.enterprise_id',
                DB::raw('enterprises.name as enterprise_name'),
                'enterprises.category',
                DB::raw('enterprises.address as address_text'),
                'enterprises.is_active',
                'enterprises.created_at',
                'enterprises.updated_at',
                DB::raw('COUNT(services.service_id) as services_count')
            )
            ->where('enterprises.is_active', true)
            ->groupBy(
                'enterprises.enterprise_id',
                'enterprises.name',
                'enterprises.category',
                'enterprises.address',
                'enterprises.is_active',
                'enterprises.created_at',
                'enterprises.updated_at'
            )
            ->orderBy('services_count', 'desc')
            ->limit(6)
            ->get();

        // If user is logged in, get customer dashboard data
        $recent_orders = null;
        if (session()->has('user_id')) {
            $userId = session('user_id');
            
            // Get customer stats
            $stats['total_orders'] = DB::table('customer_orders')
                ->where('customer_id', $userId)
                ->count();
            
            $stats['pending_orders'] = DB::table('customer_orders')
                ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Pending')
                ->count();
            
            $stats['completed_orders'] = DB::table('customer_orders')
                ->join('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Delivered')
                ->count();
            
            $stats['total_assets'] = DB::table('user_designs')
                ->where('user_id', $userId)
                ->count() + DB::table('order_design_files')
                ->join('customer_orders', 'order_design_files.purchase_order_id', '=', 'customer_orders.purchase_order_id')
                ->where('customer_orders.customer_id', $userId)
                ->count();

            // Get recent orders (alias enterprise name for views)
            $recent_orders = DB::table('customer_orders')
                ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
                ->leftJoin('statuses', 'customer_orders.status_id', '=', 'statuses.status_id')
                ->select(
                    'customer_orders.*',
                    DB::raw('enterprises.name as enterprise_name'),
                    'statuses.status_name'
                )
                ->where('customer_orders.customer_id', $userId)
                ->orderBy('customer_orders.created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('home.index', compact('stats', 'enterprises', 'recent_orders'));
    }

    private function getTestimonials()
    {
        // For now, return sample testimonials
        // In production, this would come from a testimonials table
        return collect([
            (object)[
                'name' => 'Maria Santos',
                'position' => 'Small Business Owner',
                'rating' => 5,
                'comment' => 'UniPrint made ordering business cards so easy! The AI design tool helped me create professional cards in minutes.',
                'initial' => 'M'
            ],
            (object)[
                'name' => 'Juan Dela Cruz',
                'position' => 'Event Organizer',
                'rating' => 5,
                'comment' => 'Fast delivery and excellent quality! The real-time tracking kept me updated throughout the process.',
                'initial' => 'J'
            ],
            (object)[
                'name' => 'Anna Martinez',
                'position' => 'Marketing Manager',
                'rating' => 5,
                'comment' => 'Great platform connecting me with local print shops. Competitive prices and reliable service!',
                'initial' => 'A'
            ],
        ]);
    }

    /**
     * List all enterprises
     * 
     * @return \Illuminate\View\View
     */
    public function enterprises()
    {
        $enterprises = \App\Models\Enterprise::with('services')
            ->orderBy('name')
            ->get();
        
        // Categories can be derived from enterprise names or set manually
        $categories = ['All', 'Print Shop', 'T-Shirt Printing', 'Large Format', 'Business Cards'];

        return view('public.enterprises.index', compact('enterprises', 'categories'));
    }

    /**
     * Browse enterprises with filtering
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function browseEnterprises(Request $request)
    {
        $query = Enterprise::where('is_active', true)
            ->withCount('services');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search by name (column is 'name')
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->input('sort', 'name');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'services':
                $query->orderBy('services_count', 'desc');
                break;
            default:
                $query->orderBy('name');
        }

        $enterprises = $query->paginate(12);
        
        $categories = Enterprise::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->sort();

        return view('home.browse', compact('enterprises', 'categories'));
    }

    /**
     * Show enterprise details (public view)
     * 
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function showEnterprise($id)
    {
        $enterprise = \App\Models\Enterprise::where('enterprise_id', $id)->firstOrFail();
        
        $services = \App\Models\Service::where('enterprise_id', $id)
            ->where('is_active', true)
            ->with('customizationOptions')
            ->get();

        return view('public.enterprises.show', compact('enterprise', 'services'));
    }

    /**
     * Show service details
     * 
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function showService($id)
    {
        $service = \App\Models\Service::where('service_id', $id)
            ->where('is_active', true)
            ->with(['enterprise', 'customizationOptions'])
            ->firstOrFail();

        // Group customization options by type
        $customizationGroups = $service->customizationOptions->groupBy('option_type');

        return view('public.services.show', compact('service', 'customizationGroups'));
    }

}
