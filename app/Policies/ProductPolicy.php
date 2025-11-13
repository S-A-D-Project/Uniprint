<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Product Policy
 * 
 * Enforces authorization rules for product operations
 * 
 * @package App\Policies
 */
class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view products
        return true;
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        // Admin can view all products
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only view their enterprise's products
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $product->enterprise_id;
        }

        // Customers can view all available products
        if ($user->role_type === 'customer') {
            return $product->is_available;
        }

        return false;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        // Only business users and admins can create products
        return in_array($user->role_type, ['business_user', 'admin']);
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Admin can update all products
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only update their enterprise's products
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $product->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Admin can delete all products
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only delete their enterprise's products
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $product->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->delete($user, $product);
    }

    /**
     * Determine if the user can permanently delete the product.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only admins can permanently delete
        return $user->role_type === 'admin';
    }
}
