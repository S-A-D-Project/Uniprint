<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceMarketplaceController extends Controller
{
    /**
     * Display the service marketplace
     */
    public function index()
    {
        $userId = session('user_id');
        
        Log::info('Service marketplace accessed', ['user_id' => $userId]);
        
        if (!$userId) {
            Log::warning('Unauthorized marketplace access attempt - no user ID in session');
            return redirect()->route('login');
        }
        
        try {
            // Get user information
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                Log::warning('User not found in database', ['user_id' => $userId]);
                return redirect()->route('login');
            }
            
            Log::info('Loading service marketplace', ['user_id' => $userId]);
            
            // Get marketplace statistics
            $totalServices = DB::table('services')->where('is_active', true)->count();
            $totalProviders = DB::table('enterprises')->where('is_active', true)->count();
            
            // Get featured services (high review count or recent)
            $featuredServices = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                ->where('s.is_active', true)
                ->select([
                    's.service_id as product_id',
                    's.service_name as product_name',
                    's.description',
                    's.base_price',
                    'e.name as enterprise_name',
                    'e.address as location',
                    DB::raw('0 as rating'),
                    DB::raw('0 as review_count')
                ])
                ->orderBy('s.created_at', 'desc')
                ->limit(8)
                ->get();
            
            // Get popular categories (mock data for demo)
            $categories = [
                ['id' => 'business', 'name' => 'Business Services', 'icon' => 'briefcase', 'count' => 45, 'color' => 'blue'],
                ['id' => 'design', 'name' => 'Design & Creative', 'icon' => 'palette', 'count' => 32, 'color' => 'purple'],
                ['id' => 'marketing', 'name' => 'Marketing', 'icon' => 'megaphone', 'count' => 28, 'color' => 'green'],
                ['id' => 'printing', 'name' => 'Printing & Production', 'icon' => 'printer', 'count' => 67, 'color' => 'orange'],
                ['id' => 'digital', 'name' => 'Digital Services', 'icon' => 'monitor', 'count' => 19, 'color' => 'indigo'],
                ['id' => 'consulting', 'name' => 'Consulting', 'icon' => 'users', 'count' => 23, 'color' => 'pink']
            ];
            
            // Get recent searches for this user (mock data)
            $recentSearches = [
                'business cards', 'flyer design', 't-shirt printing', 'logo design'
            ];
            
            // Get saved services count
            $savedServicesCount = DB::table('saved_services')
                ->where('user_id', $userId)
                ->count();
            
            Log::info('Service marketplace data loaded successfully', [
                'user_id' => $userId,
                'total_services' => $totalServices,
                'total_providers' => $totalProviders,
                'featured_services_count' => $featuredServices->count(),
                'saved_services_count' => $savedServicesCount
            ]);
            
            return view('customer.service-marketplace', compact(
                'user',
                'totalServices',
                'totalProviders',
                'featuredServices',
                'categories',
                'recentSearches',
                'savedServicesCount'
            ));
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database query error in service marketplace', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);
            
            return back()->with('error', 'Unable to load marketplace data. Please try again later.');
            
        } catch (\Exception $e) {
            Log::error('Unexpected error in service marketplace', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }
}
