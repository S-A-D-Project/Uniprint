<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CustomizationGroup;
use App\Models\CustomizationOption;
use App\Models\CustomizationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Customization Service
 * 
 * Handles product customization logic, validation, and dependencies
 * 
 * @package App\Services
 */
class CustomizationService
{
    /**
     * Validate selected customizations against rules
     *
     * @param int $productId
     * @param array $selectedOptions Array of option IDs
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateCustomizations(int $productId, array $selectedOptions): array
    {
        $errors = [];
        
        // Get product with customization groups and rules
        $product = Product::with([
            'customizationGroups.customizationOptions',
            'customizationGroups.rules'
        ])->findOrFail($productId);

        // Check required groups
        foreach ($product->customizationGroups as $group) {
            if ($group->is_required) {
                $hasSelection = false;
                foreach ($group->customizationOptions as $option) {
                    if (in_array($option->option_id, $selectedOptions)) {
                        $hasSelection = true;
                        break;
                    }
                }
                
                if (!$hasSelection) {
                    $errors[] = "Selection required for: {$group->group_name}";
                }
            }
        }

        // Validate customization rules
        $rules = CustomizationRule::where('is_active', true)
            ->whereIn('customization_group_id', $product->customizationGroups->pluck('group_id'))
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->isConditionMet($selectedOptions)) {
                $message = $rule->error_message ?? $this->getDefaultRuleMessage($rule);
                $errors[] = $message;
            }
        }

        // Check for conflicting options within same group
        foreach ($product->customizationGroups as $group) {
            if (!$group->allows_multiple) {
                $selectedInGroup = array_intersect(
                    $selectedOptions,
                    $group->customizationOptions->pluck('option_id')->toArray()
                );
                
                if (count($selectedInGroup) > 1) {
                    $errors[] = "Only one option allowed for: {$group->group_name}";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get available options based on current selections
     *
     * @param int $productId
     * @param array $currentSelections
     * @return array
     */
    public function getAvailableOptions(int $productId, array $currentSelections): array
    {
        $cacheKey = "available_options_{$productId}_" . md5(json_encode($currentSelections));
        
        return Cache::remember($cacheKey, 300, function () use ($productId, $currentSelections) {
            $product = Product::with([
                'customizationGroups.customizationOptions',
                'customizationGroups.rules'
            ])->findOrFail($productId);

            $availableOptions = [];

            foreach ($product->customizationGroups as $group) {
                $groupOptions = [];
                
                foreach ($group->customizationOptions as $option) {
                    // Check if option is enabled by current selections
                    if ($this->isOptionAvailable($option->option_id, $currentSelections, $group->rules)) {
                        $groupOptions[] = [
                            'option_id' => $option->option_id,
                            'option_name' => $option->option_name,
                            'price_modifier' => $option->price_modifier,
                            'is_available' => true,
                        ];
                    } else {
                        $groupOptions[] = [
                            'option_id' => $option->option_id,
                            'option_name' => $option->option_name,
                            'price_modifier' => $option->price_modifier,
                            'is_available' => false,
                            'reason' => 'Incompatible with current selections',
                        ];
                    }
                }

                $availableOptions[$group->group_id] = [
                    'group_name' => $group->group_name,
                    'is_required' => $group->is_required,
                    'allows_multiple' => $group->allows_multiple,
                    'options' => $groupOptions,
                ];
            }

            return $availableOptions;
        });
    }

    /**
     * Check if an option is available based on current selections
     *
     * @param int $optionId
     * @param array $currentSelections
     * @param $rules
     * @return bool
     */
    private function isOptionAvailable(int $optionId, array $currentSelections, $rules): bool
    {
        foreach ($rules as $rule) {
            if (!$rule->is_active) {
                continue;
            }

            // Check if this option is the dependent option
            if ($rule->dependent_option_id === $optionId) {
                // Check if required conditions are met
                switch ($rule->rule_type) {
                    case 'requires':
                        if (!in_array($rule->required_option_id, $currentSelections)) {
                            return false;
                        }
                        break;
                    
                    case 'conflicts':
                        if (in_array($rule->required_option_id, $currentSelections)) {
                            return false;
                        }
                        break;
                }
            }
        }

        return true;
    }

    /**
     * Get default error message for a rule
     *
     * @param CustomizationRule $rule
     * @return string
     */
    private function getDefaultRuleMessage(CustomizationRule $rule): string
    {
        $dependent = CustomizationOption::find($rule->dependent_option_id);
        $required = CustomizationOption::find($rule->required_option_id);

        switch ($rule->rule_type) {
            case 'requires':
                return "'{$dependent?->option_name}' requires '{$required?->option_name}' to be selected.";
            
            case 'conflicts':
                return "'{$dependent?->option_name}' cannot be combined with '{$required?->option_name}'.";
            
            default:
                return "Invalid customization combination.";
        }
    }

    /**
     * Create customization rule
     *
     * @param array $data
     * @return CustomizationRule
     * @throws Exception
     */
    public function createRule(array $data): CustomizationRule
    {
        DB::beginTransaction();
        
        try {
            $rule = CustomizationRule::create($data);
            
            // Clear related caches
            $this->clearCustomizationCache($rule->customization_group_id);
            
            DB::commit();
            
            Log::info('Customization rule created', [
                'rule_id' => $rule->rule_id,
                'rule_type' => $rule->rule_type,
            ]);
            
            return $rule;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create customization rule', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update customization rule
     *
     * @param int $ruleId
     * @param array $data
     * @return CustomizationRule
     * @throws Exception
     */
    public function updateRule(int $ruleId, array $data): CustomizationRule
    {
        try {
            $rule = CustomizationRule::findOrFail($ruleId);
            $rule->update($data);
            
            $this->clearCustomizationCache($rule->customization_group_id);
            
            Log::info('Customization rule updated', ['rule_id' => $ruleId]);
            
            return $rule->fresh();
            
        } catch (Exception $e) {
            Log::error('Failed to update customization rule', [
                'rule_id' => $ruleId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Clear customization cache
     *
     * @param int $groupId
     * @return void
     */
    private function clearCustomizationCache(int $groupId): void
    {
        $group = CustomizationGroup::find($groupId);
        if ($group) {
            Cache::forget("available_options_{$group->product_id}_*");
        }
    }

    /**
     * Bulk import customization configurations
     *
     * @param int $productId
     * @param array $configurations
     * @return array
     */
    public function bulkImportCustomizations(int $productId, array $configurations): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        
        try {
            foreach ($configurations as $index => $config) {
                try {
                    // Create customization group
                    $group = CustomizationGroup::create([
                        'product_id' => $productId,
                        'group_name' => $config['group_name'],
                        'is_required' => $config['is_required'] ?? false,
                        'allows_multiple' => $config['allows_multiple'] ?? false,
                        'display_order' => $config['display_order'] ?? ($index + 1),
                    ]);

                    // Create options
                    foreach ($config['options'] as $optionData) {
                        CustomizationOption::create([
                            'group_id' => $group->group_id,
                            'option_name' => $optionData['name'],
                            'price_modifier' => $optionData['price'] ?? 0,
                        ]);
                    }

                    $results['success']++;
                    
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$index}: " . $e->getMessage();
                }
            }

            DB::commit();
            
            Log::info('Bulk import completed', [
                'product_id' => $productId,
                'success' => $results['success'],
                'failed' => $results['failed'],
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk import failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * Export customization configurations
     *
     * @param int $productId
     * @return array
     */
    public function exportCustomizations(int $productId): array
    {
        $product = Product::with([
            'customizationGroups.customizationOptions',
            'customizationGroups.rules'
        ])->findOrFail($productId);

        $export = [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'groups' => [],
        ];

        foreach ($product->customizationGroups as $group) {
            $groupData = [
                'group_name' => $group->group_name,
                'is_required' => $group->is_required,
                'allows_multiple' => $group->allows_multiple,
                'display_order' => $group->display_order,
                'options' => [],
                'rules' => [],
            ];

            foreach ($group->customizationOptions as $option) {
                $groupData['options'][] = [
                    'name' => $option->option_name,
                    'price' => $option->price_modifier,
                ];
            }

            foreach ($group->rules as $rule) {
                $groupData['rules'][] = [
                    'rule_type' => $rule->rule_type,
                    'dependent_option' => $rule->dependentOption->option_name,
                    'required_option' => $rule->requiredOption?->option_name,
                    'error_message' => $rule->error_message,
                ];
            }

            $export['groups'][] = $groupData;
        }

        return $export;
    }
}
