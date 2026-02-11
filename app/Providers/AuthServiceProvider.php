<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\Enterprise;
use App\Policies\ServicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\EnterprisePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Service::class => ServicePolicy::class,
        CustomerOrder::class => OrderPolicy::class,
        Enterprise::class => EnterprisePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define custom gates for specific permissions
        
        // Admin-only gates
        Gate::define('manage-system', function ($user) {
            return $user->role_type === 'admin';
        });

        Gate::define('view-all-data', function ($user) {
            return $user->role_type === 'admin';
        });

        // Business user gates
        Gate::define('manage-enterprise', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });

        Gate::define('manage-services', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });

        Gate::define('manage-orders', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });

        Gate::define('update-order-status', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });

        // Customer gates
        Gate::define('place-orders', function ($user) {
            return $user->role_type === 'customer';
        });

        Gate::define('view-own-orders', function ($user) {
            return in_array($user->role_type, ['customer', 'business_user', 'admin']);
        });

        // Enterprise-specific gates with tenant isolation
        Gate::define('access-enterprise-data', function ($user, $enterpriseId) {
            if ($user->role_type === 'admin') {
                return true;
            }
            
            if ($user->role_type === 'business_user') {
                return $user->staff && $user->staff->enterprise_id === $enterpriseId;
            }
            
            return false;
        });

        Gate::define('configure-customizations', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });

        Gate::define('view-analytics', function ($user) {
            return in_array($user->role_type, ['admin', 'business_user']);
        });
    }
}
