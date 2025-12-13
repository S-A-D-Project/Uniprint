<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Tenant Isolation Middleware
 * 
 * Validates that users can only access data within their tenant scope
 * Provides an additional security layer beyond global scopes
 * 
 * @package App\Http\Middleware
 */
class EnsureTenantIsolation extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Log access attempt for security audit
        Log::channel('security')->info('Tenant access attempt', [
            'user_id' => $user->user_id,
            'role' => $user->role_type,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        // Admins have access to all data
        if ($user->role_type === 'admin') {
            return $next($request);
        }

        // Business users must have an enterprise association
        if ($user->role_type === 'business_user') {
            if (!$user->staff || !$user->staff->enterprise_id) {
                Log::channel('security')->warning('Business user without enterprise access', [
                    'user_id' => $user->user_id,
                    'route' => $request->route()->getName(),
                ]);
                
                abort(403, 'You must be associated with an enterprise to access this resource.');
            }
            
            // Store enterprise context in session for easy access
            session(['current_enterprise_id' => $user->staff->enterprise_id]);
        }

        // Customers must be active
        if ($user->role_type === 'customer' && !$user->is_active) {
            Log::channel('security')->warning('Inactive customer access attempt', [
                'user_id' => $user->user_id,
            ]);
            
            abort(403, 'Your account is currently inactive.');
        }

        // Validate route parameters for tenant isolation
        $this->validateRouteParameters($request, $user);

        return $next($request);
    }

    /**
     * Validate route parameters match user's tenant scope
     *
     * @param Request $request
     * @param User $user
     * @return void
     */
    private function validateRouteParameters(Request $request, $user): void
    {
        $route = $request->route();
        
        if (!$route) {
            return;
        }

        // Check enterprise_id parameter
        if ($route->hasParameter('enterprise_id')) {
            $enterpriseId = $route->parameter('enterprise_id');
            
            if ($user->role_type === 'business_user') {
                if ($user->staff->enterprise_id != $enterpriseId) {
                    Log::channel('security')->warning('Unauthorized enterprise access attempt', [
                        'user_id' => $user->user_id,
                        'attempted_enterprise_id' => $enterpriseId,
                        'user_enterprise_id' => $user->staff->enterprise_id,
                    ]);
                    
                    abort(403, 'You do not have access to this enterprise.');
                }
            }
        }

        // Check order_id parameter
        if ($route->hasParameter('order_id') || $route->hasParameter('id')) {
            $orderId = $route->parameter('order_id') ?? $route->parameter('id');
            
            if ($orderId && $user->role_type === 'customer') {
                $order = \App\Models\CustomerOrder::find($orderId);
                
                if ($order && $order->customer_account_id != $user->user_id) {
                    Log::channel('security')->warning('Unauthorized order access attempt', [
                        'user_id' => $user->user_id,
                        'attempted_order_id' => $orderId,
                        'order_owner_id' => $order->customer_account_id,
                    ]);
                    
                    abort(403, 'You do not have access to this order.');
                }
            }
        }
    }
}
