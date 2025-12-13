<?php

use App\Helpers\SafePropertyHelper;

if (!function_exists('safe_get')) {
    /**
     * Safely get a property from an object
     *
     * @param object|null $object
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    function safe_get($object, string $property, $default = null)
    {
        return SafePropertyHelper::safeGet($object, $property, $default);
    }
}

if (!function_exists('safe_nested')) {
    /**
     * Safely get a nested property from an object
     *
     * @param object|null $object
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    function safe_nested($object, string $path, $default = null)
    {
        return SafePropertyHelper::safeNested($object, $path, $default);
    }
}

if (!function_exists('enterprise_name')) {
    /**
     * Safely get enterprise name from service
     *
     * @param object|null $service
     * @return string
     */
    function enterprise_name($service): string
    {
        return SafePropertyHelper::getEnterpriseName($service);
    }
}

if (!function_exists('service_name')) {
    /**
     * Safely get service name
     *
     * @param object|null $service
     * @return string
     */
    function service_name($service): string
    {
        return SafePropertyHelper::getServiceName($service);
    }
}

// Backward compatibility
if (!function_exists('product_name')) {
    /**
     * Safely get service name (alias for service_name)
     *
     * @param object|null $service
     * @return string
     */
    function product_name($service): string
    {
        return SafePropertyHelper::getServiceName($service);
    }
}

if (!function_exists('format_price')) {
    /**
     * Safely format price
     *
     * @param mixed $price
     * @param int $decimals
     * @return string
     */
    function format_price($price, int $decimals = 2): string
    {
        return SafePropertyHelper::formatPrice($price, $decimals);
    }
}

if (!function_exists('user_name')) {
    /**
     * Safely get user name
     *
     * @param object|null $user
     * @return string
     */
    function user_name($user): string
    {
        return SafePropertyHelper::getUserName($user);
    }
}

if (!function_exists('order_status')) {
    /**
     * Safely get order status
     *
     * @param object|null $order
     * @return string
     */
    function order_status($order): string
    {
        return SafePropertyHelper::getOrderStatus($order);
    }
}
