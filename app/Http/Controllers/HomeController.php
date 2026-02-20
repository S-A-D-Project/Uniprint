<?php

namespace App\Http\Controllers;

use App\Services\TursoHttpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    private TursoHttpService $turso;

    public function __construct(TursoHttpService $turso)
    {
        $this->turso = $turso;
    }

    private function landingData(): array
    {
        return Cache::remember('home.landing_data', 300, function () {
            $stats = [
                'total_enterprises' => count($this->turso->select('enterprises')),
                'total_services' => count($this->turso->select('services')),
                'categories' => count($this->turso->query('SELECT DISTINCT category FROM services')),
            ];

            $enterprises = $this->turso->query('
                SELECT e.*, COUNT(s.service_id) as services_count 
                FROM enterprises e 
                LEFT JOIN services s ON e.enterprise_id = s.enterprise_id 
                WHERE e.is_active = 1 
                GROUP BY e.enterprise_id 
                ORDER BY services_count DESC 
                LIMIT 6
            ');

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
                $role = $this->turso->query('
                    SELECT rt.user_role_type 
                    FROM roles r 
                    JOIN role_types rt ON r.role_type_id = rt.role_type_id 
                    WHERE r.user_id = ?
                ', [$userId]);
                
                $roleType = $role[0]['user_role_type'] ?? null;

                if ($roleType === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($roleType === 'business_user') {
                    $hasEnterprise = !empty($this->turso->select('enterprises', ['owner_user_id' => $userId]));
                    if (! $hasEnterprise) {
                        $hasEnterprise = !empty($this->turso->select('staff', ['user_id' => $userId]));
                    }
                    if (! $hasEnterprise) {
                        return redirect()->route('business.onboarding');
                    }

                    $isVerified = true;
                    $enterprise = $this->turso->select('enterprises', ['owner_user_id' => $userId]);
                    if (!empty($enterprise) && true) { // Simplified for now
                        $isVerified = (bool) ($enterprise[0]['is_verified'] ?? true);
                    } elseif (!empty($this->turso->select('staff', ['user_id' => $userId]))) {
                        $staff = $this->turso->select('staff', ['user_id' => $userId]);
                        if (!empty($staff)) {
                            $enterpriseId = $staff[0]['enterprise_id'];
                            $enterprise = $this->turso->select('enterprises', ['enterprise_id' => $enterpriseId]);
                            if (!empty($enterprise)) {
                                $isVerified = (bool) ($enterprise[0]['is_verified'] ?? true);
                            }
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
        $sql = "SELECT * FROM enterprises WHERE is_active = 1";
        $params = [];
        
        if (request()->filled('category')) {
            $sql .= " AND category = ?";
            $params[] = request('category');
        }
        
        if (request()->filled('search')) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . request('search') . '%';
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $enterprises = $this->turso->query($sql, $params);
        
        // Get categories
        $categories = $this->turso->query('
            SELECT DISTINCT category 
            FROM enterprises 
            WHERE is_active = 1 AND category IS NOT NULL AND category != ""
            ORDER BY category
        ');
        
        $categories = array_column($categories, 'category');

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
        $sql = "SELECT e.*, COUNT(s.service_id) as services_count 
                FROM enterprises e 
                LEFT JOIN services s ON e.enterprise_id = s.enterprise_id 
                WHERE e.is_active = 1";
        $params = [];
        
        if ($request->filled('category')) {
            $sql .= " AND e.category = ?";
            $params[] = $request->category;
        }
        
        if ($request->filled('search')) {
            $sql .= " AND e.name LIKE ?";
            $params[] = '%' . $request->search . '%';
        }
        
        $sql .= " GROUP BY e.enterprise_id";
        
        // Sort
        $sortBy = $request->input('sort', 'name');
        switch ($sortBy) {
            case 'name':
                $sql .= " ORDER BY e.name";
                break;
            case 'services':
                $sql .= " ORDER BY services_count DESC";
                break;
            default:
                $sql .= " ORDER BY e.name";
        }
        
        $enterprises = $this->turso->query($sql, $params);
        
        $categories = $this->turso->query('
            SELECT DISTINCT category 
            FROM enterprises 
            WHERE is_active = 1 AND category IS NOT NULL AND category != ""
            ORDER BY category
        ');
        
        $categories = array_column($categories, 'category');

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
        $enterprise = $this->turso->select('enterprises', ['enterprise_id' => $id, 'is_active' => 1]);
        
        if (empty($enterprise)) {
            abort(404);
        }
        
        $enterprise = $enterprise[0];
        
        // Get rating if reviews table exists
        $rating = $this->turso->query('
            SELECT AVG(r.rating) as rating, COUNT(*) as review_count 
            FROM reviews r 
            JOIN services s ON r.service_id = s.service_id 
            WHERE s.enterprise_id = ?
        ', [$id]);
        
        if (!empty($rating)) {
            $enterprise['rating'] = round((float) ($rating[0]['rating'] ?? 0), 1);
            $enterprise['review_count'] = (int) ($rating[0]['review_count'] ?? 0);
        }
        
        $services = $this->turso->select('services', ['enterprise_id' => $id, 'is_active' => 1]);

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
        $service = $this->turso->select('services', ['service_id' => $id, 'is_active' => 1]);
        
        if (empty($service)) {
            abort(404);
        }
        
        $service = $service[0];
        
        // Get enterprise info
        $enterprise = $this->turso->select('enterprises', ['enterprise_id' => $service['enterprise_id'], 'is_active' => 1]);
        if (empty($enterprise)) {
            abort(404);
        }
        
        $service['enterprise'] = $enterprise[0];
        
        // Get customization options
        $customizationOptions = $this->turso->select('service_customization_options', ['service_id' => $id]);
        $service['customization_options'] = $customizationOptions;

        return view('public.services.show', compact('service'));
    }

}
