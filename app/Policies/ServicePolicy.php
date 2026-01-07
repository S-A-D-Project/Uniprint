<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

/**
 * Service Policy
 * 
 * Enforces authorization rules for service operations
 * 
 * @package App\Policies
 */
class ServicePolicy
{
    /**
     * Determine if the user can view any services.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view services
        return true;
    }

    /**
     * Determine if the user can view the service.
     */
    public function view(User $user, Service $service): bool
    {
        // Admin can view all services
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only view their enterprise's services
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $service->enterprise_id;
        }

        // Customers can view all available services
        if ($user->role_type === 'customer') {
            return (bool) $service->is_active;
        }

        return false;
    }

    /**
     * Determine if the user can create services.
     */
    public function create(User $user): bool
    {
        // Only business users and admins can create services
        return in_array($user->role_type, ['business_user', 'admin']);
    }

    /**
     * Determine if the user can update the service.
     */
    public function update(User $user, Service $service): bool
    {
        // Admin can update all services
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only update their enterprise's services
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $service->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the service.
     */
    public function delete(User $user, Service $service): bool
    {
        // Admin can delete all services
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only delete their enterprise's services
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $service->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can restore the service.
     */
    public function restore(User $user, Service $service): bool
    {
        return $this->delete($user, $service);
    }

    /**
     * Determine if the user can permanently delete the service.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        // Only admins can permanently delete
        return $user->role_type === 'admin';
    }
}
