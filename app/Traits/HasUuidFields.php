<?php

namespace App\Traits;

use App\Utils\UuidHelper;
use Illuminate\Database\Eloquent\Builder;

trait HasUuidFields
{
    /**
     * Scope to safely query by UUID
     */
    public function scopeWhereUuid(Builder $query, $column, $operator, $value = null)
    {
        return UuidHelper::whereUuid($query, $column, $operator, $value);
    }

    /**
     * Scope to safely query whereIn with UUIDs
     */
    public function scopeWhereInUuid(Builder $query, $column, $values)
    {
        return UuidHelper::whereInUuid($query, $column, $values);
    }

    /**
     * Override the find method to handle UUID casting
     */
    public static function findByUuid($id)
    {
        $uuid = UuidHelper::castToUuid($id);
        return static::where(static::getKeyName(), $uuid)->first();
    }

    /**
     * Override the findOrFail method to handle UUID casting
     */
    public static function findByUuidOrFail($id)
    {
        $uuid = UuidHelper::castToUuid($id);
        return static::where(static::getKeyName(), $uuid)->firstOrFail();
    }

    /**
     * Get the route key for the model (ensure UUID format)
     */
    public function getRouteKey()
    {
        $key = parent::getRouteKey();
        return UuidHelper::castToUuid($key);
    }

    /**
     * Retrieve the model for a bound value (handle UUID casting)
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        $uuid = UuidHelper::castToUuid($value);
        
        return $this->where($field, $uuid)->first();
    }

    /**
     * Set UUID attribute with validation
     */
    public function setUuidAttribute($key, $value)
    {
        if ($value !== null) {
            $value = UuidHelper::castToUuid($value);
        }
        $this->attributes[$key] = $value;
    }

    /**
     * Get UUID attribute with validation
     */
    public function getUuidAttribute($key)
    {
        $value = $this->attributes[$key] ?? null;
        return $value ? UuidHelper::castToUuid($value) : null;
    }

    /**
     * Boot the trait
     */
    protected static function bootHasUuidFields()
    {
        // Automatically cast UUID fields when creating
        static::creating(function ($model) {
            foreach ($model->getUuidFields() as $field) {
                if (isset($model->attributes[$field]) && $model->attributes[$field] !== null) {
                    $model->attributes[$field] = UuidHelper::castToUuid($model->attributes[$field]);
                }
            }
        });

        // Automatically cast UUID fields when updating
        static::updating(function ($model) {
            foreach ($model->getUuidFields() as $field) {
                if (isset($model->attributes[$field]) && $model->attributes[$field] !== null) {
                    $model->attributes[$field] = UuidHelper::castToUuid($model->attributes[$field]);
                }
            }
        });
    }

    /**
     * Get the UUID fields for this model
     * Override this method in your model to specify UUID fields
     */
    protected function getUuidFields()
    {
        return [
            $this->getKeyName(), // Primary key
            'enterprise_id',     // Common foreign key
            'product_id',        // Common foreign key
            'customer_id',       // Common foreign key
            'user_id',          // Common foreign key
        ];
    }
}
