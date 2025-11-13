<?php

namespace App\Traits;

/**
 * SafePropertyAccess Trait
 * 
 * Provides defensive programming methods for safe property access
 * to prevent "Undefined property" errors in the application.
 */
trait SafePropertyAccess
{
    /**
     * Safely get a property value with a default fallback
     *
     * @param object|array $object The object or array to access
     * @param string $property The property name to access
     * @param mixed $default The default value if property doesn't exist
     * @return mixed The property value or default
     */
    public function safeGet($object, string $property, $default = null)
    {
        if (is_object($object)) {
            return property_exists($object, $property) ? $object->$property : $default;
        }
        
        if (is_array($object)) {
            return array_key_exists($property, $object) ? $object[$property] : $default;
        }
        
        return $default;
    }

    /**
     * Safely format a date property
     *
     * @param object|array $object The object containing the date
     * @param string $property The date property name
     * @param string $format The desired date format
     * @param string $default Default value if date is invalid
     * @return string Formatted date or default
     */
    public function safeDate($object, string $property, string $format = 'M d, Y', string $default = 'N/A'): string
    {
        $date = $this->safeGet($object, $property);
        
        if (!$date) {
            return $default;
        }
        
        try {
            if (is_string($date)) {
                return date($format, strtotime($date));
            }
            
            if (method_exists($date, 'format')) {
                return $date->format($format);
            }
            
            return $default;
        } catch (\Exception $e) {
            \Log::warning("Date formatting error: " . $e->getMessage(), [
                'property' => $property,
                'value' => $date
            ]);
            return $default;
        }
    }

    /**
     * Safely format a number property
     *
     * @param object|array $object The object containing the number
     * @param string $property The number property name
     * @param int $decimals Number of decimal places
     * @param mixed $default Default value if number is invalid
     * @return string Formatted number or default
     */
    public function safeNumber($object, string $property, int $decimals = 2, $default = 0): string
    {
        $value = $this->safeGet($object, $property, $default);
        
        if (!is_numeric($value)) {
            return number_format($default, $decimals);
        }
        
        return number_format((float) $value, $decimals);
    }

    /**
     * Safely get a nested property (e.g., user.profile.name)
     *
     * @param object|array $object The root object
     * @param string $path Dot-separated property path
     * @param mixed $default Default value if any part of path doesn't exist
     * @return mixed The nested property value or default
     */
    public function safeNested($object, string $path, $default = null)
    {
        $properties = explode('.', $path);
        $current = $object;
        
        foreach ($properties as $property) {
            if (is_object($current) && property_exists($current, $property)) {
                $current = $current->$property;
            } elseif (is_array($current) && array_key_exists($property, $current)) {
                $current = $current[$property];
            } else {
                return $default;
            }
        }
        
        return $current;
    }

    /**
     * Safely check if a property exists and is not empty
     *
     * @param object|array $object The object to check
     * @param string $property The property name
     * @return bool True if property exists and is not empty
     */
    public function hasProperty($object, string $property): bool
    {
        $value = $this->safeGet($object, $property);
        return !empty($value);
    }

    /**
     * Get the first available property from a list of property names
     *
     * @param object|array $object The object to search
     * @param array $properties Array of property names to try
     * @param mixed $default Default value if none found
     * @return mixed The first available property value or default
     */
    public function safeFirstAvailable($object, array $properties, $default = null)
    {
        foreach ($properties as $property) {
            $value = $this->safeGet($object, $property);
            if ($value !== null) {
                return $value;
            }
        }
        
        return $default;
    }

    /**
     * Safely get a property and ensure it's a string
     *
     * @param object|array $object The object to access
     * @param string $property The property name
     * @param string $default Default string value
     * @return string The property as string or default
     */
    public function safeString($object, string $property, string $default = ''): string
    {
        $value = $this->safeGet($object, $property, $default);
        return (string) $value;
    }

    /**
     * Safely get a boolean property
     *
     * @param object|array $object The object to access
     * @param string $property The property name
     * @param bool $default Default boolean value
     * @return bool The property as boolean or default
     */
    public function safeBool($object, string $property, bool $default = false): bool
    {
        $value = $this->safeGet($object, $property, $default);
        return (bool) $value;
    }
}
