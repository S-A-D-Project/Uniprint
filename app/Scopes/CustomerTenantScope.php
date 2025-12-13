<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Customer Tenant Scope
 * 
 * Automatically filters queries to only return data belonging to the
 * authenticated customer, ensuring strict data isolation for customer data.
 * 
 * @package App\Scopes
 */
class CustomerTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin can see all data
        if ($user->role_type === 'admin') {
            return;
        }

        // Business users need enterprise-level access (handled by EnterpriseTenantScope)
        if ($user->role_type === 'business_user') {
            // For customer orders, filter by enterprise
            if ($model->getTable() === 'customer_orders') {
                $enterpriseId = $user->staff?->enterprise_id;
                if ($enterpriseId) {
                    $builder->where('enterprise_id', $enterpriseId);
                } else {
                    $builder->whereRaw('1 = 0');
                }
            }
            return;
        }

        // Customers can only see their own data
        if ($user->role_type === 'customer') {
            $builder->where($model->getTable() . '.customer_account_id', $user->user_id);
        }
    }

    /**
     * Extend the query builder with helper methods
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutCustomerTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('forCustomer', function (Builder $builder, int $customerId) {
            return $builder->withoutGlobalScope($this)
                ->where('customer_account_id', $customerId);
        });
    }
}
