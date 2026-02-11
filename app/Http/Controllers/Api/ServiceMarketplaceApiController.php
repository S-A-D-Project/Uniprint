<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ServiceMarketplaceApiController extends Controller
{
    private function isPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    private function applyEnterpriseVisibilityConstraints($query): void
    {
        if (Schema::hasColumn('enterprises', 'is_active')) {
            $query->where('e.is_active', true);
        }

        if (Schema::hasColumn('enterprises', 'is_verified')) {
            $query->where('e.is_verified', true);
        }
    }

    /**
     * Get marketplace services with filtering and pagination
     */
    public function getServices(Request $request)
    {
        try {
            $userId = Auth::user()?->user_id;
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $page = max(1, (int) $request->get('page', 1));
            $limit = 12;

            $cacheKey = 'marketplace.services.' . md5(json_encode([
                'page' => $page,
                'sort_by' => (string) $request->get('sort_by', 'relevance'),
                'category' => (string) $request->get('category', ''),
                'enterprise_id' => (string) $request->get('enterprise_id', ''),
                'search' => (string) $request->get('search', ''),
                'price_range' => (string) $request->get('price_range', ''),
                'service_type' => (string) $request->get('service_type', ''),
            ]));

            $cached = Cache::remember($cacheKey, 60, function () use ($request, $limit, $page) {
                $reviewsTableExists = Schema::hasTable('reviews');

                $query = DB::table('services as s')
                    ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                    ->where('s.is_active', true);

                $this->applyEnterpriseVisibilityConstraints($query);

                if ($reviewsTableExists) {
                    $reviewsAgg = DB::table('reviews')
                        ->select('service_id', DB::raw('AVG(rating) as rating'), DB::raw('COUNT(*) as review_count'))
                        ->groupBy('service_id');

                    $query->leftJoinSub($reviewsAgg, 'ra', function ($join) {
                        $join->on('s.service_id', '=', 'ra.service_id');
                    });
                }

                $selectFields = [
                    's.service_id',
                    's.service_name as title',
                    's.description',
                    's.base_price as price',
                    's.base_price as original_price',
                    'e.name as provider_name',
                    'e.address as location',
                    Schema::hasColumn('services', 'fulfillment_type')
                        ? 's.fulfillment_type as service_type'
                        : DB::raw("'pickup' as service_type"),
                    Schema::hasColumn('services', 'image_path')
                        ? 's.image_path'
                        : DB::raw('NULL as image_path'),
                ];

                if ($reviewsTableExists) {
                    $selectFields[] = DB::raw('COALESCE(ra.rating, 0) as rating');
                    $selectFields[] = DB::raw('COALESCE(ra.review_count, 0) as review_count');
                } else {
                    $selectFields[] = DB::raw('0 as rating');
                    $selectFields[] = DB::raw('0 as review_count');
                }

                $query->select($selectFields);

                $this->applyFilters($query, $request);
                $this->applySorting($query, $request->get('sort_by', 'relevance'));

                $paginator = $query->paginate($limit, ['*'], 'page', $page);

                $disk = config('filesystems.default', 'public');
                $formattedServices = collect($paginator->items())->map(function ($service) use ($disk) {
                    $imageUrl = null;
                    $path = $service->image_path ?? null;
                    if (!empty($path)) {
                        try {
                            $imageUrl = Storage::disk($disk)->url($path);
                        } catch (\Throwable $e) {
                            try {
                                $imageUrl = Storage::disk('public')->url($path);
                            } catch (\Throwable $e2) {
                                $imageUrl = null;
                            }
                        }
                    }
                    return [
                        'service_id' => $service->service_id,
                        'title' => $service->title,
                        'description' => $service->description ?? '',
                        'price' => number_format($service->price, 2),
                        'original_price' => null,
                        'provider_name' => $service->provider_name,
                        'location' => $service->location ?: 'Online',
                        'rating' => round($service->rating, 1),
                        'review_count' => $service->review_count,
                        'service_type' => $service->service_type,
                        'is_featured' => $service->review_count > 10,
                        'image_url' => $imageUrl,
                    ];
                });

                return [
                    'services' => $formattedServices,
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ];
            });

            Log::info('Marketplace services loaded', [
                'user_id' => $userId,
                'page' => $page,
                'total' => $cached['total'] ?? null,
                'filters' => $request->all()
            ]);

            return response()->json([
                'services' => $cached['services'],
                'total' => $cached['total'],
                'page' => $page,
                'has_more' => $cached['has_more']
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
            $userId = Auth::user()?->user_id;
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $isPostgres = $this->isPostgres();
            $cacheKey = 'search_suggestions_' . md5($query);
            $suggestions = Cache::remember($cacheKey, 300, function () use ($query, $isPostgres) {
                $q = DB::table('services as s')
                    ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                    ->where('s.is_active', true);

                $this->applyEnterpriseVisibilityConstraints($q);

                if ($isPostgres) {
                    $q->where('s.service_name', 'ILIKE', "%{$query}%");
                } else {
                    $q->whereRaw('LOWER(s.service_name) LIKE ?', ['%' . strtolower($query) . '%']);
                }

                $rows = $q
                    ->select('s.service_name as term', DB::raw('COUNT(*) as count'))
                    ->groupBy('s.service_name')
                    ->orderByDesc(DB::raw('COUNT(*)'))
                    ->limit(5)
                    ->get();

                return $rows->map(function ($row) {
                    return [
                        'term' => $row->term,
                        'category' => 'Service',
                        'count' => (int) ($row->count ?? 0),
                    ];
                });
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
            $userId = Auth::user()?->user_id;
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if reviews table exists
            $reviewsTableExists = Schema::hasTable('reviews');
            
            $query = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id');

            $this->applyEnterpriseVisibilityConstraints($query);
            
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
                'e.address as address',
                'e.contact_number as phone',
                Schema::hasColumn('enterprises', 'email')
                    ? 'e.email as email'
                    : DB::raw('NULL as email'),
                Schema::hasColumn('services', 'image_path')
                    ? 's.image_path'
                    : DB::raw('NULL as image_path')
            ];
            
            if ($reviewsTableExists) {
                $selectFields[] = DB::raw('COALESCE(AVG(r.rating), 0) as rating');
                $selectFields[] = DB::raw('COUNT(r.review_id) as review_count');
                $groupBy = ['s.service_id', 's.service_name', 's.description', 's.base_price', 'e.name', 'e.address', 'e.contact_number'];
                if (Schema::hasColumn('enterprises', 'email')) {
                    $groupBy[] = 'e.email';
                }
                if (Schema::hasColumn('services', 'image_path')) {
                    $groupBy[] = 's.image_path';
                }
                $query->groupBy($groupBy);
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
                ->when(Schema::hasColumn('enterprises', 'is_active'), function ($q) {
                    $q->where('e.is_active', true);
                })
                ->when(Schema::hasColumn('enterprises', 'is_verified'), function ($q) {
                    $q->where('e.is_verified', true);
                })
                ->where('s.is_active', true)
                ->where('s.service_id', '!=', $serviceId)
                ->where('s.enterprise_id', '=', DB::raw("(SELECT enterprise_id FROM services WHERE service_id = '{$serviceId}')"))
                ->limit(4)
                ->select([
                    's.service_id',
                    's.service_name as title',
                    's.base_price as price',
                    'e.name as provider_name',
                    Schema::hasColumn('services', 'image_path')
                        ? 's.image_path'
                        : DB::raw('NULL as image_path')
                ])
                ->get();

            // Format service
            $disk = config('filesystems.default', 'public');
            $imageUrl = null;
            if (!empty($service->image_path ?? null)) {
                try {
                    $imageUrl = Storage::disk($disk)->url($service->image_path);
                } catch (\Throwable $e) {
                    try {
                        $imageUrl = Storage::disk('public')->url($service->image_path);
                    } catch (\Throwable $e2) {
                        $imageUrl = null;
                    }
                }
            }

            $formattedService = [
                'service_id' => $service->service_id,
                'title' => $service->title,
                'description' => $service->description,
                'price' => number_format($service->price, 2),
                'provider_name' => $service->provider_name,
                'location' => $service->location ?: 'Online',
                'address' => $service->address,
                'phone' => $service->phone,
                'email' => $service->email,
                'rating' => round($service->rating, 1),
                'review_count' => $service->review_count,
                'image_url' => $imageUrl,
                'related_services' => $relatedServices->map(function($related) use ($disk) {
                    $relatedImageUrl = null;
                    if (!empty($related->image_path ?? null)) {
                        try {
                            $relatedImageUrl = Storage::disk($disk)->url($related->image_path);
                        } catch (\Throwable $e) {
                            try {
                                $relatedImageUrl = Storage::disk('public')->url($related->image_path);
                            } catch (\Throwable $e2) {
                                $relatedImageUrl = null;
                            }
                        }
                    }
                    return [
                        'service_id' => $related->service_id,
                        'title' => $related->title,
                        'price' => number_format($related->price, 2),
                        'provider_name' => $related->provider_name,
                        'image_url' => $relatedImageUrl
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
            $userId = Auth::user()?->user_id;
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $serviceId = $request->get('service_id');
            
            if (!$serviceId) {
                return response()->json(['error' => 'Service ID required'], 400);
            }

            // Check if service exists
            $serviceQuery = DB::table('services as s')
                ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                ->where('s.service_id', $serviceId)
                ->where('s.is_active', true);

            $this->applyEnterpriseVisibilityConstraints($serviceQuery);

            $service = $serviceQuery->select('s.*')->first();

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
            $userId = Auth::user()?->user_id;
            
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $categories = Cache::remember('marketplace_categories_v2', 3600, function () {
                $allCountQuery = DB::table('services as s')
                    ->join('enterprises as e', 's.enterprise_id', '=', 'e.enterprise_id')
                    ->where('s.is_active', true);

                if (Schema::hasColumn('enterprises', 'is_active')) {
                    $allCountQuery->where('e.is_active', true);
                }
                if (Schema::hasColumn('enterprises', 'is_verified')) {
                    $allCountQuery->where('e.is_verified', true);
                }

                $allCount = $allCountQuery->count();

                if (!Schema::hasColumn('enterprises', 'category')) {
                    return [
                        ['id' => 'all', 'name' => 'All Services', 'icon' => 'grid', 'count' => (int) $allCount],
                    ];
                }

                $byCategory = DB::table('enterprises as e')
                    ->join('services as s', 'e.enterprise_id', '=', 's.enterprise_id')
                    ->where('e.is_active', true)
                    ->when(Schema::hasColumn('enterprises', 'is_verified'), function ($q) {
                        $q->where('e.is_verified', true);
                    })
                    ->where('s.is_active', true)
                    ->select('e.category', DB::raw('COUNT(DISTINCT s.service_id) as count'))
                    ->groupBy('e.category')
                    ->orderBy('e.category')
                    ->get();

                $mapped = $byCategory->map(function ($row) {
                    $name = (string) ($row->category ?? 'General');
                    return [
                        'id' => $name,
                        'name' => $name,
                        'icon' => 'grid',
                        'count' => (int) ($row->count ?? 0),
                    ];
                })->values()->all();

                return array_merge([
                    ['id' => 'all', 'name' => 'All Services', 'icon' => 'grid', 'count' => (int) $allCount],
                ], $mapped);
            });

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
        $category = $request->get('category', 'all');
        if (!empty($category) && $category !== 'all') {
            if (Schema::hasColumn('enterprises', 'category')) {
                $query->where('e.category', $category);
            } elseif (Schema::hasColumn('services', 'category')) {
                $query->where('s.category', $category);
            }
        }

        $enterpriseId = $request->get('enterprise_id');
        if (!empty($enterpriseId)) {
            $query->where('s.enterprise_id', $enterpriseId);
        }

        $search = (string) $request->get('search', '');
        if ($search !== '') {
            $isPostgres = $this->isPostgres();

            $query->where(function ($q) use ($search, $isPostgres) {
                if ($isPostgres) {
                    $q->where('s.service_name', 'ILIKE', "%{$search}%")
                        ->orWhere('s.description', 'ILIKE', "%{$search}%")
                        ->orWhere('e.name', 'ILIKE', "%{$search}%");
                } else {
                    $like = '%' . strtolower($search) . '%';
                    $q->whereRaw('LOWER(s.service_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(s.description, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(e.name) LIKE ?', [$like]);
                }
            });
        }

        $location = (string) $request->get('location', '');
        if ($location !== '') {
            if ($this->isPostgres()) {
                $query->where('e.address', 'ILIKE', "%{$location}%");
            } else {
                $query->whereRaw('LOWER(COALESCE(e.address, \'\')) LIKE ?', ['%' . strtolower($location) . '%']);
            }
        }

        $minPrice = $request->get('min_price');
        if (is_numeric($minPrice)) {
            $query->where('s.base_price', '>=', (float) $minPrice);
        }

        $maxPrice = $request->get('max_price');
        if (is_numeric($maxPrice)) {
            $query->where('s.base_price', '<=', (float) $maxPrice);
        }

        $priceRange = $request->get('price_range', '');
        if (!empty($priceRange) && !is_numeric($minPrice) && !is_numeric($maxPrice)) {
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
            $query->whereRaw('COALESCE(ra.rating, 0) >= ?', [$minRating]);
        }

        $fulfillmentType = (string) $request->get('fulfillment_type', '');
        if ($fulfillmentType !== '' && Schema::hasColumn('services', 'fulfillment_type')) {
            if ($fulfillmentType === 'pickup') {
                $query->whereIn('s.fulfillment_type', ['pickup', 'both']);
            } elseif ($fulfillmentType === 'delivery') {
                $query->whereIn('s.fulfillment_type', ['delivery', 'both']);
            } elseif ($fulfillmentType === 'both') {
                $query->where('s.fulfillment_type', 'both');
            }
        }
    }

    public function getEnterprises(Request $request)
    {
        try {
            $userId = Auth::user()?->user_id;
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $term = (string) $request->get('q', '');
            $limit = min(30, max(5, (int) $request->get('limit', 20)));

            $q = DB::table('enterprises as e')
                ->join('services as s', 'e.enterprise_id', '=', 's.enterprise_id')
                ->where('s.is_active', true)
                ->select(
                    'e.enterprise_id',
                    'e.name',
                    DB::raw('COUNT(DISTINCT s.service_id) as services_count')
                )
                ->groupBy('e.enterprise_id', 'e.name');

            if (Schema::hasColumn('enterprises', 'is_active')) {
                $q->where('e.is_active', true);
            }
            if (Schema::hasColumn('enterprises', 'is_verified')) {
                $q->where('e.is_verified', true);
            }

            if ($term !== '') {
                if ($this->isPostgres()) {
                    $q->where('e.name', 'ILIKE', "%{$term}%");
                } else {
                    $q->whereRaw('LOWER(e.name) LIKE ?', ['%' . strtolower($term) . '%']);
                }
            }

            $rows = $q
                ->orderBy('e.name')
                ->limit($limit)
                ->get();

            return response()->json($rows->map(function ($row) {
                return [
                    'enterprise_id' => $row->enterprise_id,
                    'name' => $row->name,
                    'services_count' => (int) ($row->services_count ?? 0),
                ];
            }));
        } catch (\Throwable $e) {
            Log::error('Failed to load marketplace enterprises', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to load enterprises'], 500);
        }
    }

    public function getLocations(Request $request)
    {
        try {
            $userId = Auth::user()?->user_id;
            if (!$userId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $limit = min(50, max(5, (int) $request->get('limit', 30)));

            $q = DB::table('enterprises as e')
                ->join('services as s', 'e.enterprise_id', '=', 's.enterprise_id')
                ->where('s.is_active', true)
                ->whereNotNull('e.address')
                ->where('e.address', '!=', '')
                ->select('e.address')
                ->distinct()
                ->limit(500);

            if (Schema::hasColumn('enterprises', 'is_active')) {
                $q->where('e.is_active', true);
            }
            if (Schema::hasColumn('enterprises', 'is_verified')) {
                $q->where('e.is_verified', true);
            }

            $rows = $q->get();

            $seen = [];
            $locations = [];

            foreach ($rows as $row) {
                $address = trim((string) ($row->address ?? ''));
                if ($address === '') {
                    continue;
                }

                $parts = array_values(array_filter(array_map('trim', explode(',', $address)), function ($v) {
                    return $v !== '';
                }));

                $token = $parts ? end($parts) : $address;
                $token = trim((string) $token);
                if ($token === '') {
                    continue;
                }

                $key = mb_strtolower($token);
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $locations[] = [
                    'id' => $token,
                    'name' => $token,
                ];

                if (count($locations) >= $limit) {
                    break;
                }
            }

            return response()->json($locations);
        } catch (\Throwable $e) {
            Log::error('Failed to load marketplace locations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to load locations'], 500);
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
                }
                $query->orderBy('s.created_at', 'desc');
                break;
            case 'price-low':
                $query->orderBy('s.base_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('s.base_price', 'desc');
                break;
            case 'rating':
                if ($reviewsTableExists) {
                    $query->orderBy('rating', 'desc');
                }
                $query->orderBy('s.created_at', 'desc');
                break;
            case 'newest':
                $query->orderBy('s.created_at', 'desc');
                break;
            case 'relevance':
            default:
                $query->orderBy('s.created_at', 'desc');
                break;
        }
    }
}
