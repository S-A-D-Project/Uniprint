<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ServiceMarketplaceApiController extends Controller
{
    /**
     * Get marketplace services with filtering and pagination
     */
    public function getServices(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $page = (int) $request->get('page', 1);
            $limit = 12;
            $offset = ($page - 1) * $limit;

            // Check if reviews table exists
            $reviewsTableExists = Schema::hasTable('reviews');
            
            // Build query for services
            $query = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id');
            
            if ($reviewsTableExists) {
                $query->leftJoin('reviews as r', 's.service_id', '=', 'r.service_id');
            }
            
            $query->where('s.is_active', true);
            
            $selectFields = [
                's.service_id',
                's.service_name as title',
                's.description',
                's.base_price as price',
                's.base_price as original_price', // Can be modified for discounts
                'e.name as provider_name',
                'e.address as location',
                DB::raw("'printing' as service_type"),
                DB::raw('false as is_featured'),
                DB::raw("'/placeholder-service.jpg' as image_url")
            ];
            
            if ($reviewsTableExists) {
                $selectFields[] = DB::raw('COALESCE(AVG(r.rating), 0) as rating');
                $selectFields[] = DB::raw('COUNT(r.review_id) as review_count');
                $query->groupBy('s.service_id', 's.service_name', 's.description', 's.base_price', 'e.name', 'e.address');
            } else {
                $selectFields[] = DB::raw('0 as rating');
                $selectFields[] = DB::raw('0 as review_count');
            }
            
            $query->select($selectFields);

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request->get('sort_by', 'relevance'));

            // Get total count
            $totalQuery = clone $query;
            $total = $totalQuery->count();

            // Get paginated results
            $services = $query->offset($offset)->limit($limit)->get();

            // Format services
            $formattedServices = $services->map(function($service) {
                return [
                    'service_id' => $service->service_id,
                    'title' => $service->title,
                    'description' => $service->description ?: 'Professional service with quality assurance and fast delivery.',
                    'price' => number_format($service->price, 2),
                    'original_price' => $service->price > 100 ? number_format($service->price * 1.2, 2) : null,
                    'provider_name' => $service->provider_name,
                    'location' => $service->location ?: 'Online',
                    'rating' => round($service->rating, 1),
                    'review_count' => $service->review_count,
                    'service_type' => $service->service_type,
                    'is_featured' => $service->review_count > 10,
                    'image_url' => $service->image_url
                ];
            });

            $hasMore = ($offset + $services->count()) < $total;

            Log::info('Marketplace services loaded', [
                'user_id' => $userId,
                'page' => $page,
                'total' => $total,
                'filters' => $request->all()
            ]);

            return response()->json([
                'services' => $formattedServices,
                'total' => $total,
                'page' => $page,
                'has_more' => $hasMore
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load marketplace services', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to load services'], 500);
        }
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            // Cache suggestions for performance
            $cacheKey = "search_suggestions_" . md5($query);
            $suggestions = Cache::remember($cacheKey, 300, function() use ($query) {
                return DB::table('services as s')
                    ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                    ->where('s.is_active', true)
                    ->where('s.service_name', 'ILIKE', "%{$query}%")
                    ->select(
                        's.service_name as term',
                        DB::raw("'Printing Services' as category"),
                        DB::raw('(
                            SELECT COUNT(*) FROM services 
                            WHERE service_name ILIKE "%' . $query . '%"
                        ) as count')
                    )
                    ->distinct()
                    ->limit(5)
                    ->get();
            });

            Log::info('Search suggestions loaded', [
                'user_id' => $userId,
                'query' => $query,
                'count' => $suggestions->count()
            ]);

            return response()->json($suggestions);

        } catch (\Exception $e) {
            Log::error('Failed to load search suggestions', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load suggestions'], 500);
        }
    }

    /**
     * Get service details
     */
    public function getServiceDetails($serviceId)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if reviews table exists
            $reviewsTableExists = Schema::hasTable('reviews');
            
            $query = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id');
            
            if ($reviewsTableExists) {
                $query->leftJoin('reviews as r', 's.service_id', '=', 'r.service_id');
            }
            
            $query->where('s.service_id', $serviceId)
                ->where('s.is_active', true);
            
            $selectFields = [
                's.service_id',
                's.service_name as title',
                's.description',
                's.base_price as price',
                'e.name as provider_name',
                'e.address as location',
                'e.contact_number as phone',
                DB::raw("'contact@example.com' as email"),
                DB::raw("'/placeholder-service.jpg' as image_url")
            ];
            
            if ($reviewsTableExists) {
                $selectFields[] = DB::raw('COALESCE(AVG(r.rating), 0) as rating');
                $selectFields[] = DB::raw('COUNT(r.review_id) as review_count');
                $query->groupBy('s.service_id', 's.service_name', 's.description', 's.base_price', 'e.name', 'e.address', 'e.contact_number');
            } else {
                $selectFields[] = DB::raw('0 as rating');
                $selectFields[] = DB::raw('0 as review_count');
            }
            
            $query->select($selectFields);
            
            $service = $query->first();

            if (!$service) {
                return response()->json(['error' => 'Service not found'], 404);
            }

            // Get related services
            $relatedServices = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                ->where('s.is_active', true)
                ->where('s.service_id', '!=', $serviceId)
                ->where('s.enterprise_id', '=', DB::raw("(SELECT enterprise_id FROM services WHERE service_id = '{$serviceId}')"))
                ->limit(4)
                ->select([
                    's.service_id',
                    's.service_name as title',
                    's.base_price as price',
                    'e.name as provider_name',
                    DB::raw("'/placeholder-service.jpg' as image_url")
                ])
                ->get();

            // Format service
            $formattedService = [
                'service_id' => $service->service_id,
                'title' => $service->title,
                'description' => $service->description ?: 'Professional service with quality assurance and fast delivery. We provide high-quality printing solutions tailored to your needs.',
                'price' => number_format($service->price, 2),
                'provider_name' => $service->provider_name,
                'location' => $service->location ?: 'Online',
                'address' => $service->address,
                'phone' => $service->phone,
                'email' => $service->email,
                'rating' => round($service->rating, 1),
                'review_count' => $service->review_count,
                'image_url' => $service->image_url,
                'related_services' => $relatedServices->map(function($related) {
                    return [
                        'service_id' => $related->service_id,
                        'title' => $related->title,
                        'price' => number_format($related->price, 2),
                        'provider_name' => $related->provider_name,
                        'image_url' => $related->image_url
                    ];
                })
            ];

            Log::info('Service details loaded', [
                'user_id' => $userId,
                'service_id' => $serviceId
            ]);

            return response()->json($formattedService);

        } catch (\Exception $e) {
            Log::error('Failed to load service details', [
                'user_id' => $userId ?? null,
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load service details'], 500);
        }
    }

    /**
     * Toggle favorite service
     */
    public function toggleFavorite(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $serviceId = $request->get('service_id');
            
            if (!$serviceId) {
                return response()->json(['error' => 'Service ID required'], 400);
            }

            // Check if service exists
            $service = DB::table('services')
                ->where('service_id', $serviceId)
                ->where('is_active', true)
                ->first();

            if (!$service) {
                return response()->json(['error' => 'Service not found'], 404);
            }

            // Toggle in saved services
            $existing = DB::table('saved_services')
                ->where('user_id', $userId)
                ->where('service_id', $serviceId)
                ->first();

            if ($existing) {
                // Remove from favorites
                DB::table('saved_services')
                    ->where('user_id', $userId)
                    ->where('service_id', $serviceId)
                    ->delete();
                
                $isFavorited = false;
            } else {
                // Add to favorites
                DB::table('saved_services')->insert([
                    'saved_service_id' => DB::raw('gen_random_uuid()'),
                    'user_id' => $userId,
                    'service_id' => $serviceId,
                    'quantity' => 1,
                    'unit_price' => $service->base_price,
                    'total_price' => $service->base_price,
                    'saved_at' => now()
                ]);
                
                $isFavorited = true;
            }

            // Get updated count
            $savedServicesCount = DB::table('saved_services')
                ->where('user_id', $userId)
                ->count();

            Log::info('Service favorite toggled', [
                'user_id' => $userId,
                'service_id' => $serviceId,
                'is_favorited' => $isFavorited
            ]);

            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited,
                'saved_services_count' => $savedServicesCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle favorite service', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to toggle favorite'], 500);
        }
    }

    /**
     * Get marketplace categories
     */
    public function getCategories()
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Cache categories for performance
            $categories = Cache::remember('marketplace_categories', 3600, function() {
                return [
                    ['id' => 'all', 'name' => 'All Services', 'icon' => 'grid', 'count' => 0],
                    ['id' => 'business', 'name' => 'Business Services', 'icon' => 'briefcase', 'count' => 0],
                    ['id' => 'design', 'name' => 'Design & Creative', 'icon' => 'palette', 'count' => 0],
                    ['id' => 'marketing', 'name' => 'Marketing', 'icon' => 'megaphone', 'count' => 0],
                    ['id' => 'printing', 'name' => 'Printing & Production', 'icon' => 'printer', 'count' => 0],
                    ['id' => 'digital', 'name' => 'Digital Services', 'icon' => 'monitor', 'count' => 0],
                    ['id' => 'consulting', 'name' => 'Consulting', 'icon' => 'users', 'count' => 0]
                ];
            });

            // Get actual counts
            foreach ($categories as &$category) {
                if ($category['id'] === 'all') {
                    $category['count'] = DB::table('services')->where('is_active', true)->count();
                } else {
                    // For demo, assign some counts - in real implementation, this would be based on actual categorization
                    $category['count'] = rand(5, 50);
                }
            }

            Log::info('Marketplace categories loaded', ['user_id' => $userId]);

            return response()->json($categories);

        } catch (\Exception $e) {
            Log::error('Failed to load marketplace categories', [
                'user_id' => $userId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load categories'], 500);
        }
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        // Category filter
        $category = $request->get('category', 'all');
        if ($category !== 'all') {
            // In real implementation, this would filter by actual categories
            // For demo, we'll apply some basic filtering
            switch($category) {
                case 'printing':
                    $query->where('s.service_name', 'ILIKE', '%print%');
                    break;
                case 'design':
                    $query->where('s.service_name', 'ILIKE', '%design%');
                    break;
                case 'business':
                    $query->where('s.service_name', 'ILIKE', '%business%');
                    break;
            }
        }

        // Search filter
        $search = $request->get('search', '');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('s.service_name', 'ILIKE', "%{$search}%")
                  ->orWhere('s.description', 'ILIKE', "%{$search}%")
                  ->orWhere('e.name', 'ILIKE', "%{$search}%");
            });
        }

        // Location filter
        $location = $request->get('location', '');
        if (!empty($location)) {
            $query->where('e.address', 'ILIKE', "%{$location}%");
        }

        // Price range filter
        $priceRange = $request->get('price_range', '');
        if (!empty($priceRange)) {
            switch($priceRange) {
                case '0-500':
                    $query->where('s.base_price', '<', 500);
                    break;
                case '500-2000':
                    $query->whereBetween('s.base_price', [500, 2000]);
                    break;
                case '2000-5000':
                    $query->whereBetween('s.base_price', [2000, 5000]);
                    break;
                case '5000+':
                    $query->where('s.base_price', '>', 5000);
                    break;
            }
        }

        // Rating filter
        $rating = $request->get('rating', '');
        if (!empty($rating) && Schema::hasTable('reviews')) {
            $minRating = (float) str_replace('+', '', $rating);
            $query->havingRaw('COALESCE(AVG(r.rating), 0) >= ?', [$minRating]);
        }

        // Service type filter
        $serviceType = $request->get('service_type', '');
        if (!empty($serviceType)) {
            // In real implementation, this would filter by actual service types
            // For demo, we'll apply some basic logic
            switch($serviceType) {
                case 'online':
                    $query->where('e.city', 'ILIKE', '%online%');
                    break;
                case 'onsite':
                    $query->whereNotNull('e.address');
                    break;
                case 'delivery':
                    $query->where('s.service_name', 'ILIKE', '%delivery%');
                    break;
            }
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, $sortBy)
    {
        $reviewsTableExists = Schema::hasTable('reviews');
        
        switch($sortBy) {
            case 'popular':
                if ($reviewsTableExists) {
                    $query->orderBy('review_count', 'desc');
                } else {
                    $query->orderBy('s.created_at', 'desc');
                }
                break;
            case 'price-low':
                $query->orderBy('s.base_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('s.base_price', 'desc');
                break;
            case 'rating':
                if ($reviewsTableExists) {
                    $query->orderByRaw('COALESCE(AVG(r.rating), 0) DESC');
                } else {
                    $query->orderBy('s.created_at', 'desc');
                }
                break;
            case 'newest':
                $query->orderBy('s.created_at', 'desc');
                break;
            case 'relevance':
            default:
                $query->orderBy('s.base_price', 'asc');
                break;
        }
    }
}
