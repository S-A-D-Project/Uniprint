<?php

namespace App\Helpers;

/**
 * SafePropertyHelper
 * 
 * Global helper functions for safe property access to prevent
 * "Undefined property" errors throughout the application.
 */
class SafePropertyHelper
{
    /**
     * Safely get a nested property with null coalescing
     *
     * @param object|null $object The object to access
     * @param string $property The property name
     * @param mixed $default Default value if property doesn't exist
     * @return mixed The property value or default
     */
    public static function safeGet($object, string $property, $default = null)
    {
        if (!$object || !is_object($object)) {
            return $default;
        }

        return property_exists($object, $property) ? $object->$property : $default;
    }

    /**
     * Safely get a nested property (e.g., product.enterprise.name)
     *
     * @param object|null $object The root object
     * @param string $path Dot-separated property path
     * @param mixed $default Default value if any part of path doesn't exist
     * @return mixed The nested property value or default
     */
    public static function safeNested($object, string $path, $default = null)
    {
        if (!$object) {
            return $default;
        }

        $properties = explode('.', $path);
        $current = $object;
        
        foreach ($properties as $property) {
            if (is_object($current) && property_exists($current, $property)) {
                $current = $current->$property;
            } else {
                return $default;
            }
        }
        
        return $current;
    }

    /**
     * Safely get enterprise name from service
     *
     * @param object|null $service The service object
     * @return string The enterprise name or default
     */
    public static function getEnterpriseName($service): string
    {
        return static::safeNested($service, 'enterprise.name', 'Unknown Shop');
    }

    /**
     * Safely get service name
     *
     * @param object|null $service The service object
     * @return string The service name or default
     */
    public static function getServiceName($service): string
    {
        return static::safeGet($service, 'service_name', 'Unknown Service');
    }


    /**
     * Safely format price
     *
     * @param mixed $price The price value
     * @param int $decimals Number of decimal places
     * @return string Formatted price
     */
    public static function formatPrice($price, int $decimals = 2): string
    {
        if (!is_numeric($price)) {
            return '₱0.00';
        }
        
        return '₱' . number_format((float) $price, $decimals);
    }

    /**
     * Safely get user name
     *
     * @param object|null $user The user object
     * @return string The user name or default
     */
    public static function getUserName($user): string
    {
        if (!$user) {
            return 'Unknown User';
        }

        $firstName = static::safeGet($user, 'first_name', '');
        $lastName = static::safeGet($user, 'last_name', '');
        
        if ($firstName || $lastName) {
            return trim($firstName . ' ' . $lastName);
        }
        
        return static::safeGet($user, 'username', 'Unknown User');
    }

    /**
     * Safely get order status
     *
     * @param object|null $order The order object
     * @return string The order status or default
     */
    public static function getOrderStatus($order): string
    {
        return static::safeGet($order, 'status_name', 'Unknown Status');
    }

    /**
     * Check if object has property and it's not empty
     *
     * @param object|null $object The object to check
     * @param string $property The property name
     * @return bool True if property exists and is not empty
     */
    public static function hasProperty($object, string $property): bool
    {
        if (!$object || !is_object($object)) {
            return false;
        }

        $value = static::safeGet($object, $property);
        return !empty($value);
    }
}
