<?php

namespace App\Models;

use Illuminate\Support\Collection;

class SavedServiceCollection
{
    protected $userId;
    protected $items;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->loadItems();
    }

    protected function loadItems()
    {
        $this->items = SavedService::with(['service.enterprise', 'customizationOptions'])
            ->where('user_id', $this->userId)
            ->get();
    }

    /**
     * Get all saved services (items)
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Get total number of items (sum of quantities)
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get total amount
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum('total_price');
    }

    /**
     * Get subtotal (same as total for saved services)
     */
    public function getSubtotalAttribute()
    {
        return $this->getTotalAmountAttribute();
    }

    /**
     * Count of saved services (distinct services, not quantities)
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Check if collection is not empty
     */
    public function isNotEmpty()
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Add item to saved services
     */
    public function addItem($serviceId, $quantity = 1, $customizations = [], $specialInstructions = null)
    {
        $service = SavedService::saveService(
            $this->userId,
            $serviceId,
            $quantity,
            $customizations,
            $specialInstructions
        );
        
        $this->loadItems(); // Refresh items
        return $service;
    }

    /**
     * Remove item from saved services
     */
    public function removeItem($savedServiceId)
    {
        $result = SavedService::removeService($this->userId, $savedServiceId);
        $this->loadItems(); // Refresh items
        return $result;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity($savedServiceId, $quantity)
    {
        $result = SavedService::updateServiceQuantity($this->userId, $savedServiceId, $quantity);
        $this->loadItems(); // Refresh items
        return $result;
    }

    /**
     * Clear all saved services
     */
    public function clear()
    {
        $result = SavedService::clearServices($this->userId);
        $this->loadItems(); // Refresh items
        return $result;
    }

    /**
     * Get items with relationships (for compatibility)
     */
    public function itemsWithRelationships()
    {
        return $this->items;
    }

    /**
     * Magic method to handle dynamic property access
     */
    public function __get($key)
    {
        if (method_exists($this, 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute')) {
            return $this->{'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute'}();
        }
        
        return null;
    }

    /**
     * Magic method to handle dynamic method calls
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->items, $method)) {
            return $this->items->$method(...$parameters);
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
