<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pricing Rule Model
 * 
 * Handles dynamic pricing calculations based on various conditions
 * 
 * @package App\Models
 */
class PricingRule extends Model
{
    protected $primaryKey = 'pricing_rule_id';

    protected $fillable = [
        'service_id',
        'rule_name',
        'rule_type',
        'conditions',
        'calculation_type',
        'value',
        'tier_structure',
        'priority',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'conditions' => 'array',
        'tier_structure' => 'array',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the service this pricing rule belongs to
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    /**
     * Check if rule is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate price adjustment based on rule
     *
     * @param float $basePrice
     * @param int $quantity
     * @param array $context Additional context (customizations, rush order, etc.)
     * @return float
     */
    public function calculateAdjustment(float $basePrice, int $quantity, array $context = []): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if (!$this->conditionsMet($quantity, $context)) {
            return 0;
        }

        switch ($this->calculation_type) {
            case 'percentage':
                return $basePrice * ($this->value / 100);

            case 'fixed_amount':
                return $this->value;

            case 'tiered':
                return $this->calculateTieredPrice($basePrice, $quantity);

            default:
                return 0;
        }
    }

    /**
     * Check if rule conditions are met
     */
    private function conditionsMet(int $quantity, array $context): bool
    {
        $conditions = $this->conditions;

        // Check quantity conditions
        if (isset($conditions['min_quantity']) && $quantity < $conditions['min_quantity']) {
            return false;
        }

        if (isset($conditions['max_quantity']) && $quantity > $conditions['max_quantity']) {
            return false;
        }

        // Check customization conditions
        if (isset($conditions['required_customizations'])) {
            $selectedCustomizations = $context['customizations'] ?? [];
            foreach ($conditions['required_customizations'] as $required) {
                if (!in_array($required, $selectedCustomizations)) {
                    return false;
                }
            }
        }

        // Check rush order condition
        if (isset($conditions['is_rush']) && $conditions['is_rush']) {
            if (!($context['is_rush'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate tiered pricing
     */
    private function calculateTieredPrice(float $basePrice, int $quantity): float
    {
        if (!$this->tier_structure) {
            return 0;
        }

        foreach ($this->tier_structure as $tier) {
            $minQty = $tier['min_quantity'] ?? 0;
            $maxQty = $tier['max_quantity'] ?? PHP_INT_MAX;

            if ($quantity >= $minQty && $quantity <= $maxQty) {
                if ($tier['type'] === 'percentage') {
                    return $basePrice * ($tier['value'] / 100);
                } else {
                    return $tier['value'];
                }
            }
        }

        return 0;
    }

    /**
     * Scope to get active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }
}
