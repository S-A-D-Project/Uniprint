<?php

namespace App\Models;

use App\Scopes\EnterpriseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Workflow Model
 * 
 * Defines custom workflows for different order types
 * 
 * @package App\Models
 */
class OrderWorkflow extends Model
{
    protected $primaryKey = 'workflow_id';

    protected $fillable = [
        'enterprise_id',
        'workflow_name',
        'workflow_type',
        'description',
        'workflow_stages',
        'conditions',
        'requires_approval',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'workflow_stages' => 'array',
        'conditions' => 'array',
        'requires_approval' => 'boolean',
        'is_default' => 'boolean',
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
     * Get the enterprise this workflow belongs to
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'enterprise_id');
    }

    /**
     * Get orders using this workflow
     */
    public function orders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'workflow_id', 'workflow_id');
    }

    /**
     * Check if workflow applies to given order context
     *
     * @param array $orderContext
     * @return bool
     */
    public function appliesTo(array $orderContext): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $conditions = $this->conditions;

        // Check if this is default workflow
        if ($this->is_default && empty($conditions)) {
            return true;
        }

        // Check priority condition
        if (isset($conditions['priority'])) {
            $orderPriority = $orderContext['priority'] ?? 'normal';
            if ($conditions['priority'] !== $orderPriority) {
                return false;
            }
        }

        // Check order amount condition
        if (isset($conditions['min_amount'])) {
            $orderAmount = $orderContext['total_amount'] ?? 0;
            if ($orderAmount < $conditions['min_amount']) {
                return false;
            }
        }

        // Check product category condition
        if (isset($conditions['product_categories'])) {
            $productCategory = $orderContext['product_category'] ?? '';
            if (!in_array($productCategory, $conditions['product_categories'])) {
                return false;
            }
        }

        // Check complexity condition
        if (isset($conditions['is_complex'])) {
            $isComplex = $orderContext['is_complex'] ?? false;
            if ($conditions['is_complex'] !== $isComplex) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate estimated completion time
     *
     * @return int Total hours
     */
    public function calculateTotalDuration(): int
    {
        $totalHours = 0;

        foreach ($this->workflow_stages as $stage) {
            $totalHours += $stage['duration_hours'] ?? 0;
        }

        return $totalHours;
    }

    /**
     * Get next stage after current one
     *
     * @param string $currentStage
     * @return array|null
     */
    public function getNextStage(string $currentStage): ?array
    {
        $stages = $this->workflow_stages;
        $currentIndex = null;

        foreach ($stages as $index => $stage) {
            if ($stage['name'] === $currentStage) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null || !isset($stages[$currentIndex + 1])) {
            return null;
        }

        return $stages[$currentIndex + 1];
    }

    /**
     * Scope to get active workflows
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default workflow
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)
            ->where('is_active', true);
    }
}
