<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * User Owned Scope
 * 
 * Automatically filters queries to only return data owned by the
 * authenticated user (for assets, AI generations, etc.)
 * 
 * @package App\Scopes
 */
class UserOwnedScope implements Scope
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

        // All other users can only see their own data
        $builder->where($model->getTable() . '.user_id', $user->user_id);
    }

    /**
     * Extend the query builder with helper methods
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutUserOwned', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('forUser', function (Builder $builder, int $userId) {
            return $builder->withoutGlobalScope($this)
                ->where('user_id', $userId);
        });
    }
}
