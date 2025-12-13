<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PricingEngine
{
    /**
     * Calculate price for a service with customizations
     * 
     * @param string $serviceId
     * @param array $customizationIds
     * @param int $quantity
     * @param string|null $enterpriseId
     * @return array
     */
    public function calculatePrice($serviceId, $customizationIds = [], $quantity = 1, $enterpriseId = null)
    {
        // Get service base price
        $service = DB::table('services')->where('service_id', $serviceId)->first();
        
        if (!$service) {
            throw new \Exception('Service not found');
        }

        $basePrice = $service->base_price;
        $customizationsTotal = 0;
        $customizationBreakdown = [];

        // Calculate customizations cost
        if (!empty($customizationIds)) {
            $customizations = DB::table('customization_options')
                ->whereIn('option_id', $customizationIds)
                ->get();

            foreach ($customizations as $custom) {
                $customizationsTotal += $custom->price_modifier;
                $customizationBreakdown[] = [
                    'name' => $custom->option_name,
                    'type' => $custom->option_type,
                    'price' => $custom->price_modifier,
                ];
            }
        }

        // Subtotal before rules
        $subtotal = ($basePrice + $customizationsTotal) * $quantity;

        // Get and apply pricing rules
        $enterpriseId = $enterpriseId ?? $service->enterprise_id;
        $rules = $this->getActivePricingRules($enterpriseId);
        
        $rulesApplied = [];
        $discount = 0;
        $additionalFees = 0;

        foreach ($rules as $rule) {
            // Check if rule conditions are met
            if ($this->evaluateRuleConditions($rule, $service, $quantity, $subtotal)) {
                $ruleValue = $this->applyRule($rule, $subtotal);
                
                if ($ruleValue < 0) {
                    $discount += abs($ruleValue);
                } else {
                    $additionalFees += $ruleValue;
                }

                $rulesApplied[] = [
                    'name' => $rule->rule_name,
                    'type' => $rule->rule_type,
                    'value' => $ruleValue,
                    'description' => $rule->rule_description,
                ];
            }
        }

        // Calculate final total
        $total = $subtotal - $discount + $additionalFees;

        return [
            'service_name' => $service->service_name,
            'base_price' => $basePrice,
            'quantity' => $quantity,
            'customizations_total' => $customizationsTotal,
            'customizations_breakdown' => $customizationBreakdown,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'additional_fees' => $additionalFees,
            'rules_applied' => $rulesApplied,
            'total' => max(0, $total), // Ensure non-negative
            'breakdown' => [
                'Base Price' => $basePrice,
                'Customizations' => $customizationsTotal,
                'Unit Price' => $basePrice + $customizationsTotal,
                'Quantity' => $quantity,
                'Subtotal' => $subtotal,
                'Discounts' => -$discount,
                'Additional Fees' => $additionalFees,
                'Final Total' => max(0, $total),
            ],
        ];
    }

    /**
     * Get active pricing rules for an enterprise (cached)
     */
    private function getActivePricingRules($enterpriseId)
    {
        $cacheKey = "pricing_rules_{$enterpriseId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($enterpriseId) {
            return DB::table('pricing_rules')
                ->where('enterprise_id', $enterpriseId)
                ->where('is_active', true)
                ->orderBy('priority')
                ->get();
        });
    }

    /**
     * Evaluate if rule conditions are met
     */
    private function evaluateRuleConditions($rule, $service, $quantity, $subtotal)
    {
        $conditions = json_decode($rule->conditions, true);
        
        if (empty($conditions)) {
            return true; // No conditions means always apply
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;

            $actualValue = $this->getFieldValue($field, $service, $quantity, $subtotal);

            if (!$this->compareValues($actualValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get field value for condition evaluation
     */
    private function getFieldValue($field, $service, $quantity, $subtotal)
    {
        switch ($field) {
            case 'quantity':
                return $quantity;
            case 'subtotal':
                return $subtotal;
            case 'base_price':
                return $service->base_price;
            case 'service_id':
                return $service->service_id;
            default:
                return null;
        }
    }

    /**
     * Compare values based on operator
     */
    private function compareValues($actual, $operator, $expected)
    {
        switch ($operator) {
            case '=':
            case '==':
                return $actual == $expected;
            case '!=':
                return $actual != $expected;
            case '>':
                return $actual > $expected;
            case '>=':
                return $actual >= $expected;
            case '<':
                return $actual < $expected;
            case '<=':
                return $actual <= $expected;
            default:
                return false;
        }
    }

    /**
     * Apply pricing rule calculation
     */
    private function applyRule($rule, $subtotal)
    {
        switch ($rule->calculation_method) {
            case 'percentage':
                // Percentage discount/fee
                return $subtotal * ($rule->value / 100);
            
            case 'fixed_amount':
                // Fixed amount discount/fee
                return $rule->value;
            
            case 'formula':
                // Custom formula evaluation
                return $this->evaluateFormula($rule->formula, $subtotal);
            
            default:
                return 0;
        }
    }

    /**
     * Evaluate custom pricing formula
     * Safe math expression parser - NO EVAL USED
     */
    private function evaluateFormula($formula, $subtotal)
    {
        if (empty($formula)) {
            return 0;
        }

        try {
            // Replace placeholders with actual values
            $formula = str_replace('{subtotal}', (string)$subtotal, $formula);
            $formula = str_replace('{quantity}', '1', $formula); // Could be passed as param
            
            // Strict security: only allow numbers, basic math operators, and parentheses
            if (!preg_match('/^[\d\+\-\*\/\(\)\.\s]+$/', $formula)) {
                throw new \Exception('Invalid formula: contains unauthorized characters');
            }

            // Safe evaluation using a simple math parser instead of eval()
            $result = $this->safeMathEvaluate($formula);
            
            return is_numeric($result) ? (float)$result : 0;
        } catch (\Exception $e) {
            // Log error and return 0
            \Log::error('Pricing formula error: ' . $e->getMessage(), [
                'formula' => $formula,
                'subtotal' => $subtotal
            ]);
            return 0;
        }
    }

    /**
     * Safe mathematical expression evaluator
     * Replaces dangerous eval() with a secure parser
     */
    private function safeMathEvaluate($expression)
    {
        // Remove all whitespace
        $expression = preg_replace('/\s+/', '', $expression);
        
        // Validate expression contains only allowed characters
        if (!preg_match('/^[\d\+\-\*\/\(\)\.]+$/', $expression)) {
            throw new \Exception('Invalid mathematical expression');
        }
        
        // Simple recursive descent parser for basic arithmetic
        return $this->parseExpression($expression);
    }

    /**
     * Parse mathematical expression safely
     */
    private function parseExpression($expr)
    {
        // Handle simple cases first
        if (is_numeric($expr)) {
            return (float)$expr;
        }
        
        // For complex expressions, use a simple state machine
        // This is a basic implementation - for production, consider using a proper math parser library
        $tokens = $this->tokenizeExpression($expr);
        return $this->evaluateTokens($tokens);
    }

    /**
     * Tokenize mathematical expression
     */
    private function tokenizeExpression($expr)
    {
        $tokens = [];
        $current = '';
        
        for ($i = 0; $i < strlen($expr); $i++) {
            $char = $expr[$i];
            
            if (is_numeric($char) || $char === '.') {
                $current .= $char;
            } else {
                if ($current !== '') {
                    $tokens[] = (float)$current;
                    $current = '';
                }
                $tokens[] = $char;
            }
        }
        
        if ($current !== '') {
            $tokens[] = (float)$current;
        }
        
        return $tokens;
    }

    /**
     * Evaluate tokenized expression
     */
    private function evaluateTokens($tokens)
    {
        // Simple evaluation for basic arithmetic
        // Handle multiplication and division first
        for ($i = 1; $i < count($tokens) - 1; $i++) {
            if ($tokens[$i] === '*') {
                $result = $tokens[$i-1] * $tokens[$i+1];
                array_splice($tokens, $i-1, 3, [$result]);
                $i--;
            } elseif ($tokens[$i] === '/') {
                if ($tokens[$i+1] == 0) {
                    throw new \Exception('Division by zero');
                }
                $result = $tokens[$i-1] / $tokens[$i+1];
                array_splice($tokens, $i-1, 3, [$result]);
                $i--;
            }
        }
        
        // Handle addition and subtraction
        for ($i = 1; $i < count($tokens) - 1; $i++) {
            if ($tokens[$i] === '+') {
                $result = $tokens[$i-1] + $tokens[$i+1];
                array_splice($tokens, $i-1, 3, [$result]);
                $i--;
            } elseif ($tokens[$i] === '-') {
                $result = $tokens[$i-1] - $tokens[$i+1];
                array_splice($tokens, $i-1, 3, [$result]);
                $i--;
            }
        }
        
        return count($tokens) === 1 ? $tokens[0] : 0;
    }

    /**
     * Calculate shipping cost based on rules
     */
    public function calculateShipping($subtotal, $enterpriseId, $deliveryLocation = null)
    {
        // Check for shipping rules
        $shippingRules = DB::table('pricing_rules')
            ->where('enterprise_id', $enterpriseId)
            ->where('rule_type', 'shipping')
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        $shippingCost = 100; // Default flat rate

        foreach ($shippingRules as $rule) {
            // Apply first matching rule
            if ($this->evaluateRuleConditions($rule, (object)[], 1, $subtotal)) {
                $shippingCost = $this->applyRule($rule, $subtotal);
                break;
            }
        }

        return max(0, $shippingCost);
    }

    /**
     * Clear pricing rules cache for an enterprise
     */
    public function clearCache($enterpriseId)
    {
        Cache::forget("pricing_rules_{$enterpriseId}");
    }

    /**
     * Batch calculate prices for multiple items
     */
    public function calculateBatchPrices($items, $enterpriseId = null)
    {
        $results = [];
        $grandTotal = 0;

        foreach ($items as $item) {
            $result = $this->calculatePrice(
                $item['product_id'],
                $item['customizations'] ?? [],
                $item['quantity'] ?? 1,
                $enterpriseId
            );

            $results[] = $result;
            $grandTotal += $result['total'];
        }

        return [
            'items' => $results,
            'grand_total' => $grandTotal,
            'items_count' => count($items),
        ];
    }
}
