<?php

namespace App\Models;

use App\Scopes\EnterpriseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Inventory Material Model
 * 
 * Tracks raw materials and inventory for printing businesses
 * 
 * @package App\Models
 */
class InventoryMaterial extends Model
{
    protected $primaryKey = 'material_id';

    protected $fillable = [
        'enterprise_id',
        'material_name',
        'sku',
        'description',
        'material_type',
        'current_stock',
        'minimum_stock',
        'unit_cost',
        'unit_of_measure',
        'supplier_name',
        'lead_time_days',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new EnterpriseTenantScope);
    }

    /**
     * Get the enterprise this material belongs to
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'enterprise_id');
    }

    /**
     * Get customization options that use this material
     */
    public function customizationOptions(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomizationOption::class,
            'material_customization_options',
            'material_id',
            'option_id'
        )->withPivot('quantity_required')
          ->withTimestamps();
    }

    /**
     * Check if material is low in stock
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Check if material is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    /**
     * Reduce stock by specified amount
     */
    public function reduceStock(float $amount): bool
    {
        if ($this->current_stock < $amount) {
            return false;
        }

        $this->current_stock -= $amount;
        $this->save();

        return true;
    }

    /**
     * Add stock
     */
    public function addStock(float $amount): void
    {
        $this->current_stock += $amount;
        $this->save();
    }

    /**
     * Calculate stock value
     */
    public function getStockValue(): float
    {
        return $this->current_stock * $this->unit_cost;
    }

    /**
     * Scope to get low stock materials
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock')
            ->where('is_active', true);
    }

    /**
     * Scope to get out of stock materials
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0)
            ->where('is_active', true);
    }
}
