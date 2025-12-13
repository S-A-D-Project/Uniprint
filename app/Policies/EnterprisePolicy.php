<?php

namespace App\Policies;

use App\Models\Enterprise;
use App\Models\User;

/**
 * Enterprise Policy
 * 
 * Enforces authorization rules for enterprise operations
 * 
 * @package App\Policies
 */
class EnterprisePolicy
{
    /**
     * Determine if the user can view any enterprises.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view enterprises
        return true;
    }

    /**
     * Determine if the user can view the enterprise.
     */
    public function view(User $user, Enterprise $enterprise): bool
    {
        // Admin can view all enterprises
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only view their own enterprise
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $enterprise->enterprise_id;
        }

        // Customers can view all active enterprises
        if ($user->role_type === 'customer') {
            return $enterprise->is_active;
        }

        return false;
    }

    /**
     * Determine if the user can create enterprises.
     */
    public function create(User $user): bool
    {
        // Only admins can create enterprises
        return $user->role_type === 'admin';
    }

    /**
     * Determine if the user can update the enterprise.
     */
    public function update(User $user, Enterprise $enterprise): bool
    {
        // Admin can update all enterprises
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only update their own enterprise
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $enterprise->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the enterprise.
     */
    public function delete(User $user, Enterprise $enterprise): bool
    {
        // Only admins can delete enterprises
        return $user->role_type === 'admin';
    }

    /**
     * Determine if the user can manage services for the enterprise.
     */
    public function manageServices(User $user, Enterprise $enterprise): bool
    {
        return $this->update($user, $enterprise);
    }

    /**
     * Determine if the user can manage staff for the enterprise.
     */
    public function manageStaff(User $user, Enterprise $enterprise): bool
    {
        return $this->update($user, $enterprise);
    }
}
