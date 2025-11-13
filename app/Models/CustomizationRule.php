<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customization Rule Model
 * 
 * Defines dependencies and constraints between customization options
 * 
 * @package App\Models
 */
class CustomizationRule extends Model
{
    protected $primaryKey = 'rule_id';

    protected $fillable = [
        'customization_group_id',
        'dependent_option_id',
        'required_option_id',
        'rule_type',
        'rule_condition',
        'error_message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rule_condition' => 'array',
    ];

    /**
     * Get the customization group this rule belongs to
     */
    public function customizationGroup(): BelongsTo
    {
        return $this->belongsTo(CustomizationGroup::class, 'customization_group_id', 'group_id');
    }

    /**
     * Get the dependent option
     */
    public function dependentOption(): BelongsTo
    {
        return $this->belongsTo(CustomizationOption::class, 'dependent_option_id', 'option_id');
    }

    /**
     * Get the required option
     */
    public function requiredOption(): BelongsTo
    {
        return $this->belongsTo(CustomizationOption::class, 'required_option_id', 'option_id');
    }

    /**
     * Validate if rule condition is met
     *
     * @param array $selectedOptions
     * @return bool
     */
    public function isConditionMet(array $selectedOptions): bool
    {
        if (!$this->is_active) {
            return true;
        }

        switch ($this->rule_type) {
            case 'requires':
                // If dependent option is selected, required option must be selected
                if (in_array($this->dependent_option_id, $selectedOptions)) {
                    return in_array($this->required_option_id, $selectedOptions);
                }
                return true;

            case 'conflicts':
                // Both options cannot be selected together
                $hasDependentOption = in_array($this->dependent_option_id, $selectedOptions);
                $hasRequiredOption = in_array($this->required_option_id, $selectedOptions);
                return !($hasDependentOption && $hasRequiredOption);

            case 'requires_any':
                // If dependent is selected, at least one from required array must be selected
                if (in_array($this->dependent_option_id, $selectedOptions)) {
                    $requiredOptions = $this->rule_condition['options'] ?? [];
                    return !empty(array_intersect($requiredOptions, $selectedOptions));
                }
                return true;

            default:
                return true;
        }
    }
}
