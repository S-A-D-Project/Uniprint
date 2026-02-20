<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Home Controller
 * 
 * Handles public-facing landing page and enterprise discovery
 * 
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    private function landingData(): array
    {
        return Cache::remember('home.landing_data', 300, function () {
            $stats = [
                'total_enterprises' => DB::table('enterprises')->count(),
                'total_services' => DB::table('services')->count(),
                'categories' => DB::table('services')->distinct()->count('category'),
            ];

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
                ->when(schema_has_column('enterprises', 'is_verified'), function ($q) {
                    $q->where('enterprises.is_verified', true);
                })
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

            $recent_orders = null;

            return [$stats, $enterprises, $recent_orders];
        });
    }

    /**
     * Display the landing page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (session()->has('user_id')) {
            $userId = session('user_id');

            try {
                $role = DB::table('roles')
                    ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                    ->where('roles.user_id', $userId)
                    ->first();

                $roleType = $role?->user_role_type;

                if ($roleType === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($roleType === 'business_user') {
                    $hasEnterprise = DB::table('enterprises')->where('owner_user_id', $userId)->exists();
                    if (! $hasEnterprise) {
                        $hasEnterprise = DB::table('staff')->where('user_id', $userId)->exists();
                    }
                    if (! $hasEnterprise) {
                        return redirect()->route('business.onboarding');
                    }

                    $isVerified = true;
                    if (DB::table('enterprises')->where('owner_user_id', $userId)->exists() && \Illuminate\Support\Facades\schema_has_column('enterprises', 'is_verified')) {
                        $isVerified = (bool) DB::table('enterprises')->where('owner_user_id', $userId)->value('is_verified');
                    } elseif (\Illuminate\Support\Facades\schema_has_table('staff') && \Illuminate\Support\Facades\schema_has_column('enterprises', 'is_verified')) {
                        $enterpriseId = DB::table('staff')->where('user_id', $userId)->value('enterprise_id');
                        if ($enterpriseId) {
                            $isVerified = (bool) DB::table('enterprises')->where('enterprise_id', $enterpriseId)->value('is_verified');
                        }
                    }

                    if (! $isVerified) {
                        [$stats, $enterprises, $recent_orders] = $this->landingData();
                        return view('home.index', compact('stats', 'enterprises', 'recent_orders'));
                    }

                    return redirect()->route('business.dashboard');
                }

                return redirect()->route('customer.dashboard');
            } catch (\Throwable $e) {
                Log::warning('HomeController@index failed to resolve dashboard redirect', [
                    'user_id' => $userId,
                    'exception' => $e,
                ]);

                return redirect()->route('customer.dashboard');
            }
        }

        // Get basic statistics
        [$stats, $enterprises, $recent_orders] = $this->landingData();

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
        $query = \App\Models\Enterprise::query()
            ->where('is_active', true);

        if (schema_has_column('enterprises', 'is_verified')) {
            $query->where('is_verified', true);
        }

        $driver = DB::connection()->getDriverName();

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        if (request()->filled('search')) {
            $needle = '%' . request('search') . '%';
            if ($driver === 'pgsql') {
                $query->where('name', 'ILIKE', $needle);
            } else {
                $query->where('name', 'LIKE', $needle);
            }
        }

        $enterprises = $query
            ->orderBy('name')
            ->paginate(12);

        if (schema_has_table('reviews')) {
            $enterpriseIds = $enterprises->pluck('enterprise_id')->filter()->values()->all();
            if (!empty($enterpriseIds)) {
                $ratings = DB::table('reviews')
                    ->join('services', 'reviews.service_id', '=', 'services.service_id')
                    ->whereIn('services.enterprise_id', $enterpriseIds)
                    ->select(
                        'services.enterprise_id',
                        DB::raw('AVG(reviews.rating) as rating'),
                        DB::raw('COUNT(*) as review_count')
                    )
                    ->groupBy('services.enterprise_id')
                    ->get()
                    ->keyBy('enterprise_id');

                $enterprises->setCollection(
                    $enterprises->getCollection()->map(function ($enterprise) use ($ratings) {
                        $row = $ratings->get($enterprise->enterprise_id);
                        $enterprise->rating = $row ? round((float) $row->rating, 1) : 0.0;
                        $enterprise->review_count = $row ? (int) $row->review_count : 0;
                        return $enterprise;
                    })
                );
            }
        }

        $categories = \App\Models\Enterprise::query()
            ->where('is_active', true)
            ->when(schema_has_column('enterprises', 'is_verified'), function ($q) {
                $q->where('is_verified', true);
            })
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

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

        if (schema_has_column('enterprises', 'is_verified')) {
            $query->where('is_verified', true);
        }

        $driver = DB::connection()->getDriverName();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search by name (column is 'name')
        if ($request->filled('search')) {
            $needle = '%' . $request->search . '%';
            if ($driver === 'pgsql') {
                $query->where('name', 'ILIKE', $needle);
            } else {
                $query->where('name', 'LIKE', $needle);
            }
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
            ->when(schema_has_column('enterprises', 'is_verified'), function ($q) {
                $q->where('is_verified', true);
            })
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
        $enterpriseQuery = \App\Models\Enterprise::where('enterprise_id', $id)
            ->where('is_active', true);
        if (schema_has_column('enterprises', 'is_verified')) {
            $enterpriseQuery->where('is_verified', true);
        }
        $enterprise = $enterpriseQuery->firstOrFail();

        if (schema_has_table('reviews')) {
            $row = DB::table('reviews')
                ->join('services', 'reviews.service_id', '=', 'services.service_id')
                ->where('services.enterprise_id', $enterprise->enterprise_id)
                ->select(
                    DB::raw('AVG(reviews.rating) as rating'),
                    DB::raw('COUNT(*) as review_count')
                )
                ->first();

            $enterprise->rating = $row ? round((float) ($row->rating ?? 0), 1) : 0.0;
            $enterprise->review_count = $row ? (int) ($row->review_count ?? 0) : 0;
        }
        
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
        $serviceQuery = \App\Models\Service::where('service_id', $id)
            ->where('is_active', true)
            ->with(['enterprise', 'customizationOptions']);

        if (schema_has_column('enterprises', 'is_verified')) {
            $serviceQuery->whereHas('enterprise', function ($q) {
                $q->where('is_active', true)->where('is_verified', true);
            });
        } else {
            $serviceQuery->whereHas('enterprise', function ($q) {
                $q->where('is_active', true);
            });
        }

        $service = $serviceQuery->firstOrFail();

        // Group customization options by type
        $customizationGroups = $service->customizationOptions->groupBy('option_type');

        return view('public.services.show', compact('service', 'customizationGroups'));
    }

}
