<?php

namespace App\Services;

use App\Models\InventoryMaterial;
use App\Models\CustomizationOption;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

/**
 * Inventory Service
 * 
 * Manages raw material inventory and stock tracking
 * 
 * @package App\Services
 */
class InventoryService
{
    /**
     * Check material availability for order
     *
     * @param array $orderItems Array of [service_id, quantity, customizations]
     * @param int $enterpriseId
     * @return array ['available' => bool, 'issues' => array]
     */
    public function checkMaterialAvailability(array $orderItems, int $enterpriseId): array
    {
        $issues = [];
        $materialRequirements = [];

        foreach ($orderItems as $item) {
            $customizationIds = $item['customizations'] ?? [];
            $quantity = $item['quantity'];

            // Get materials needed for customizations
            $options = CustomizationOption::whereIn('option_id', $customizationIds)
                ->with('materials')
                ->get();

            foreach ($options as $option) {
                foreach ($option->materials as $material) {
                    $required = $material->pivot->quantity_required * $quantity;
                    
                    if (!isset($materialRequirements[$material->material_id])) {
                        $materialRequirements[$material->material_id] = [
                            'material' => $material,
                            'required' => 0,
                        ];
                    }
                    
                    $materialRequirements[$material->material_id]['required'] += $required;
                }
            }
        }

        // Check stock levels
        foreach ($materialRequirements as $materialId => $data) {
            $material = $data['material'];
            $required = $data['required'];

            if ($material->current_stock < $required) {
                $issues[] = [
                    'material_id' => $materialId,
                    'material_name' => $material->material_name,
                    'required' => $required,
                    'available' => $material->current_stock,
                    'shortage' => $required - $material->current_stock,
                ];
            }
        }

        return [
            'available' => empty($issues),
            'issues' => $issues,
            'materials_checked' => count($materialRequirements),
        ];
    }

    /**
     * Reserve materials for an order
     *
     * @param int $orderId
     * @return bool
     * @throws Exception
     */
    public function reserveMaterials(int $orderId): bool
    {
        DB::beginTransaction();
        
        try {
            $orderItems = OrderItem::where('order_id', $orderId)
                ->with(['customizations.option.materials'])
                ->get();

            foreach ($orderItems as $item) {
                foreach ($item->customizations as $customization) {
                    $option = $customization->option;
                    
                    foreach ($option->materials as $material) {
                        $required = $material->pivot->quantity_required * $item->quantity;
                        
                        if (!$material->reduceStock($required)) {
                            throw new Exception(
                                "Insufficient stock for material: {$material->material_name}"
                            );
                        }
                    }
                }
            }

            DB::commit();
            
            Log::info('Materials reserved for order', ['order_id' => $orderId]);
            
            // Check for low stock alerts
            $this->checkLowStockAlerts();
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reserve materials', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Release reserved materials (order cancelled)
     *
     * @param int $orderId
     * @return bool
     */
    public function releaseMaterials(int $orderId): bool
    {
        DB::beginTransaction();
        
        try {
            $orderItems = OrderItem::where('order_id', $orderId)
                ->with(['customizations.option.materials'])
                ->get();

            foreach ($orderItems as $item) {
                foreach ($item->customizations as $customization) {
                    $option = $customization->option;
                    
                    foreach ($option->materials as $material) {
                        $toRelease = $material->pivot->quantity_required * $item->quantity;
                        $material->addStock($toRelease);
                    }
                }
            }

            DB::commit();
            
            Log::info('Materials released for cancelled order', ['order_id' => $orderId]);
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to release materials', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update material stock
     *
     * @param int $materialId
     * @param float $quantity
     * @param string $type 'add' or 'subtract'
     * @param string $reason
     * @return InventoryMaterial
     * @throws Exception
     */
    public function updateStock(int $materialId, float $quantity, string $type, string $reason): InventoryMaterial
    {
        try {
            $material = InventoryMaterial::findOrFail($materialId);
            
            if ($type === 'add') {
                $material->addStock($quantity);
            } else {
                if (!$material->reduceStock($quantity)) {
                    throw new Exception('Insufficient stock');
                }
            }
            
            Log::info('Material stock updated', [
                'material_id' => $materialId,
                'quantity' => $quantity,
                'type' => $type,
                'reason' => $reason,
                'new_stock' => $material->current_stock,
            ]);
            
            // Check for low stock
            if ($material->isLowStock()) {
                $this->sendLowStockAlert($material);
            }
            
            return $material->fresh();
            
        } catch (Exception $e) {
            Log::error('Failed to update stock', [
                'material_id' => $materialId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get low stock materials for enterprise
     *
     * @param int $enterpriseId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockMaterials(int $enterpriseId)
    {
        return InventoryMaterial::where('enterprise_id', $enterpriseId)
            ->lowStock()
            ->get();
    }

    /**
     * Get out of stock materials
     *
     * @param int $enterpriseId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockMaterials(int $enterpriseId)
    {
        return InventoryMaterial::where('enterprise_id', $enterpriseId)
            ->outOfStock()
            ->get();
    }

    /**
     * Check and send low stock alerts
     *
     * @return void
     */
    private function checkLowStockAlerts(): void
    {
        $lowStockMaterials = InventoryMaterial::lowStock()->get();
        
        foreach ($lowStockMaterials as $material) {
            $this->sendLowStockAlert($material);
        }
    }

    /**
     * Send low stock alert notification
     *
     * @param InventoryMaterial $material
     * @return void
     */
    private function sendLowStockAlert(InventoryMaterial $material): void
    {
        Log::warning('Low stock alert', [
            'material_id' => $material->material_id,
            'material_name' => $material->material_name,
            'current_stock' => $material->current_stock,
            'minimum_stock' => $material->minimum_stock,
            'enterprise_id' => $material->enterprise_id,
        ]);
        
        // In production, send email/SMS notification to business owners
        // Notification::send($businessOwners, new LowStockNotification($material));
    }

    /**
     * Get inventory value report
     *
     * @param int $enterpriseId
     * @return array
     */
    public function getInventoryValueReport(int $enterpriseId): array
    {
        $materials = InventoryMaterial::where('enterprise_id', $enterpriseId)
            ->where('is_active', true)
            ->get();

        $totalValue = 0;
        $lowStockValue = 0;
        $outOfStockCount = 0;
        
        $byType = [];

        foreach ($materials as $material) {
            $value = $material->getStockValue();
            $totalValue += $value;
            
            if ($material->isLowStock()) {
                $lowStockValue += $value;
            }
            
            if ($material->isOutOfStock()) {
                $outOfStockCount++;
            }
            
            $type = $material->material_type;
            if (!isset($byType[$type])) {
                $byType[$type] = [
                    'count' => 0,
                    'value' => 0,
                ];
            }
            $byType[$type]['count']++;
            $byType[$type]['value'] += $value;
        }

        return [
            'total_materials' => $materials->count(),
            'total_value' => $totalValue,
            'low_stock_value' => $lowStockValue,
            'out_of_stock_count' => $outOfStockCount,
            'by_type' => $byType,
        ];
    }

    /**
     * Suggest material substitutions
     *
     * @param int $materialId
     * @return array
     */
    public function suggestSubstitutions(int $materialId): array
    {
        $material = InventoryMaterial::findOrFail($materialId);
        
        // Find similar materials from same enterprise with available stock
        $substitutes = InventoryMaterial::where('enterprise_id', $material->enterprise_id)
            ->where('material_id', '!=', $materialId)
            ->where('material_type', $material->material_type)
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock', 'desc')
            ->limit(5)
            ->get();

        return $substitutes->map(function ($substitute) use ($material) {
            return [
                'material_id' => $substitute->material_id,
                'material_name' => $substitute->material_name,
                'current_stock' => $substitute->current_stock,
                'unit_cost' => $substitute->unit_cost,
                'cost_difference' => $substitute->unit_cost - $material->unit_cost,
                'cost_difference_percentage' => $material->unit_cost > 0 
                    ? (($substitute->unit_cost - $material->unit_cost) / $material->unit_cost) * 100 
                    : 0,
            ];
        })->toArray();
    }
}
