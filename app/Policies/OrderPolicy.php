<?php

namespace App\Policies;

use App\Models\CustomerOrder;
use App\Models\User;

/**
 * Order Policy
 * 
 * Enforces authorization rules for order operations with multi-enterprise isolation
 * 
 * @package App\Policies
 */
class OrderPolicy
{
    /**
     * Determine if the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view orders (filtered by scope)
        return true;
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, CustomerOrder $order): bool
    {
        // Admin can view all orders
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can only view orders for their enterprise
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $order->enterprise_id;
        }

        // Customers can only view their own orders
        if ($user->role_type === 'customer') {
            return $order->customer_account_id === $user->user_id;
        }

        return false;
    }

    /**
     * Determine if the user can create orders.
     */
    public function create(User $user): bool
    {
        // Only customers can create orders
        return $user->role_type === 'customer';
    }

    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, CustomerOrder $order): bool
    {
        // Admin can update all orders
        if ($user->role_type === 'admin') {
            return true;
        }

        // Business users can update orders for their enterprise (status changes)
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $order->enterprise_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the order.
     */
    public function delete(User $user, CustomerOrder $order): bool
    {
        // Only admin can delete orders
        return $user->role_type === 'admin';
    }

    /**
     * Determine if the user can update order status.
     */
    public function updateStatus(User $user, CustomerOrder $order): bool
    {
        return $this->update($user, $order);
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, CustomerOrder $order): bool
    {
        // Customers can cancel their own pending orders
        if ($user->role_type === 'customer' && $order->customer_account_id === $user->user_id) {
            return $order->current_status === 'Pending';
        }

        // Business users and admins can cancel orders
        if ($user->role_type === 'business_user') {
            return $user->staff && $user->staff->enterprise_id === $order->enterprise_id;
        }

        return $user->role_type === 'admin';
    }
}
