<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Enterprise Tenant Scope
 * 
 * Automatically filters queries to only return data belonging to the
 * authenticated user's enterprise, ensuring strict data isolation.
 * 
 * @package App\Scopes
 */
class EnterpriseTenantScope implements Scope
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

        // Business users can only see their enterprise's data
        if ($user->role_type === 'business_user') {
            $enterpriseId = $user->staff?->enterprise_id;
            
            if ($enterpriseId) {
                $builder->where($model->getTable() . '.enterprise_id', $enterpriseId);
            } else {
                // If no enterprise is associated, return no results
                $builder->whereRaw('1 = 0');
            }
        }

        // Customers don't need enterprise filtering
        // Their data is filtered by customer_account_id in CustomerTenantScope
    }

    /**
     * Extend the query builder with helper methods
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutEnterpriseTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('forEnterprise', function (Builder $builder, int $enterpriseId) {
            return $builder->withoutGlobalScope($this)
                ->where('enterprise_id', $enterpriseId);
        });
    }
}
