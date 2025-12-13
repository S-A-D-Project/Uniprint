<?php

namespace App\Services;

use App\Models\Service;
use App\Models\PricingRule;
use App\Models\CustomizationOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Pricing Service
 * 
 * Handles dynamic pricing calculations with rules, discounts, and surcharges
 * 
 * @package App\Services
 */
class PricingService
{
    /**
     * Calculate total price for service with customizations
     *
     * @param int $serviceId
     * @param int $quantity
     * @param array $customizationIds
     * @param array $context Additional context (rush, customer segment, etc.)
     * @return array
     */
    public function calculatePrice(int $serviceId, int $quantity, array $customizationIds = [], array $context = []): array
    {
        $service = Service::findOrFail($serviceId);
        
        // Base price
        $basePrice = $service->base_price;
        $subtotal = $basePrice * $quantity;
        
        // Add customization costs
        $customizationCost = $this->calculateCustomizationCost($customizationIds, $quantity);
        $subtotal += $customizationCost;
        
        // Apply pricing rules
        $rules = PricingRule::where('service_id', $serviceId)
            ->active()
            ->orderBy('priority', 'desc')
            ->get();
        
        $adjustments = [];
        $totalAdjustment = 0;
        
        foreach ($rules as $rule) {
            $adjustment = $rule->calculateAdjustment($basePrice, $quantity, array_merge($context, [
                'customizations' => $customizationIds,
            ]));
            
            if ($adjustment != 0) {
                $adjustments[] = [
                    'rule_name' => $rule->rule_name,
                    'rule_type' => $rule->rule_type,
                    'amount' => $adjustment,
                ];
                
                $totalAdjustment += $adjustment;
            }
        }
        
        $finalPrice = $subtotal + $totalAdjustment;
        
        // Ensure price doesn't go negative
        $finalPrice = max(0, $finalPrice);
        
        $breakdown = [
            'base_price' => $basePrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'customization_cost' => $customizationCost,
            'adjustments' => $adjustments,
            'total_adjustment' => $totalAdjustment,
            'final_price' => $finalPrice,
            'price_per_unit' => $finalPrice / $quantity,
        ];
        
        Log::info('Price calculated', [
            'service_id' => $serviceId,
            'quantity' => $quantity,
            'final_price' => $finalPrice,
        ]);
        
        return $breakdown;
    }

    /**
     * Calculate customization costs
     *
     * @param array $customizationIds
     * @param int $quantity
     * @return float
     */
    private function calculateCustomizationCost(array $customizationIds, int $quantity): float
    {
        if (empty($customizationIds)) {
            return 0;
        }
        
        $options = CustomizationOption::whereIn('option_id', $customizationIds)->get();
        $total = 0;
        
        foreach ($options as $option) {
            $total += $option->price_modifier * $quantity;
        }
        
        return $total;
    }

    /**
     * Get quantity-based pricing tiers
     *
     * @param int $serviceId
     * @return array
     */
    public function getPricingTiers(int $serviceId): array
    {
        $cacheKey = "pricing_tiers_{$serviceId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($serviceId) {
            $service = Service::findOrFail($serviceId);
            $tiers = [];
            
            $rules = PricingRule::where('service_id', $serviceId)
                ->where('rule_type', 'quantity_discount')
                ->where('calculation_type', 'tiered')
                ->active()
                ->get();
            
            foreach ($rules as $rule) {
                if ($rule->tier_structure) {
                    foreach ($rule->tier_structure as $tier) {
                        $tiers[] = [
                            'min_quantity' => $tier['min_quantity'] ?? 0,
                            'max_quantity' => $tier['max_quantity'] ?? null,
                            'discount_type' => $tier['type'] ?? 'percentage',
                            'discount_value' => $tier['value'] ?? 0,
                            'description' => $this->formatTierDescription($tier, $service->base_price),
                        ];
                    }
                }
            }
            
            // Sort by min quantity
            usort($tiers, fn($a, $b) => $a['min_quantity'] <=> $b['min_quantity']);
            
            return $tiers;
        });
    }

    /**
     * Format tier description for display
     *
     * @param array $tier
     * @param float $basePrice
     * @return string
     */
    private function formatTierDescription(array $tier, float $basePrice): string
    {
        $minQty = $tier['min_quantity'] ?? 0;
        $maxQty = $tier['max_quantity'] ?? null;
        $value = $tier['value'] ?? 0;
        $type = $tier['type'] ?? 'percentage';
        
        $range = $maxQty ? "{$minQty}-{$maxQty}" : "{$minQty}+";
        
        if ($type === 'percentage') {
            $discount = $value;
            $newPrice = $basePrice * (1 - $value / 100);
            return "{$range} units: {$discount}% off (${$newPrice} per unit)";
        } else {
            return "{$range} units: \${$value} off per unit";
        }
    }

    /**
     * Calculate savings from current selection
     *
     * @param int $serviceId
     * @param int $quantity
     * @param array $customizationIds
     * @return array
     */
    public function calculateSavings(int $serviceId, int $quantity, array $customizationIds = []): array
    {
        $service = Service::findOrFail($serviceId);
        
        // Calculate price without any discounts
        $regularPrice = ($service->base_price * $quantity) + 
                       $this->calculateCustomizationCost($customizationIds, $quantity);
        
        // Calculate actual price with discounts
        $priceBreakdown = $this->calculatePrice($serviceId, $quantity, $customizationIds);
        $finalPrice = $priceBreakdown['final_price'];
        
        $savings = $regularPrice - $finalPrice;
        $savingsPercentage = $regularPrice > 0 ? ($savings / $regularPrice) * 100 : 0;
        
        return [
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice,
            'savings_amount' => $savings,
            'savings_percentage' => round($savingsPercentage, 2),
            'has_savings' => $savings > 0,
        ];
    }

    /**
     * Estimate lead time based on complexity and rush status
     *
     * @param int $serviceId
     * @param int $quantity
     * @param array $customizationIds
     * @param bool $isRush
     * @return array
     */
    public function estimateLeadTime(int $serviceId, int $quantity, array $customizationIds = [], bool $isRush = false): array
    {
        $service = Service::with('enterprise')->findOrFail($serviceId);
        
        // Base lead time (in business days)
        $baseDays = 3;
        
        // Add time based on quantity
        if ($quantity > 100) {
            $baseDays += 2;
        } elseif ($quantity > 50) {
            $baseDays += 1;
        }
        
        // Add time based on customizations
        if (count($customizationIds) > 5) {
            $baseDays += 2;
        } elseif (count($customizationIds) > 2) {
            $baseDays += 1;
        }
        
        // Rush order cuts time in half but adds 50% to cost
        if ($isRush) {
            $baseDays = ceil($baseDays / 2);
            $rushFee = 0.5; // 50% surcharge
        } else {
            $rushFee = 0;
        }
        
        $estimatedDate = now()->addBusinessDays($baseDays);
        
        return [
            'business_days' => $baseDays,
            'estimated_completion' => $estimatedDate->format('Y-m-d'),
            'estimated_completion_formatted' => $estimatedDate->format('M d, Y'),
            'is_rush' => $isRush,
            'rush_fee_percentage' => $rushFee * 100,
        ];
    }
}
