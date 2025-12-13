<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UuidHelper
{
    /**
     * Validate if a string is a valid UUID
     */
    public static function isValidUuid($uuid)
    {
        if (!is_string($uuid)) {
            return false;
        }
        
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Safely cast values to UUID for database queries
     */
    public static function castToUuid($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return array_map([self::class, 'castToUuid'], $value);
        }

        // If it's already a valid UUID, return as is
        if (self::isValidUuid($value)) {
            return $value;
        }

        // If it's a numeric string, try to find the corresponding UUID
        if (is_numeric($value)) {
            return self::findUuidByNumericId($value);
        }

        // If it's a string that might be a UUID without hyphens
        if (is_string($value) && strlen($value) === 32) {
            $formatted = substr($value, 0, 8) . '-' . 
                        substr($value, 8, 4) . '-' . 
                        substr($value, 12, 4) . '-' . 
                        substr($value, 16, 4) . '-' . 
                        substr($value, 20, 12);
            
            if (self::isValidUuid($formatted)) {
                return $formatted;
            }
        }

        // If all else fails, generate a new UUID (this should be logged)
        \Log::warning('Invalid UUID value provided, generating new UUID', [
            'original_value' => $value,
            'type' => gettype($value)
        ]);
        
        return Str::uuid()->toString();
    }

    /**
     * Find UUID by numeric ID (for backward compatibility)
     */
    private static function findUuidByNumericId($numericId)
    {
        // Try to find in enterprises table
        $enterprise = DB::table('enterprises')
            ->where('enterprise_id', 'LIKE', '%' . $numericId . '%')
            ->orWhere(DB::raw('CAST(enterprise_id AS TEXT)'), 'LIKE', '%' . $numericId . '%')
            ->first();
        
        if ($enterprise) {
            return $enterprise->enterprise_id;
        }

        // Try to find in services table
        $service = DB::table('services')
            ->where('service_id', 'LIKE', '%' . $numericId . '%')
            ->orWhere(DB::raw('CAST(service_id AS TEXT)'), 'LIKE', '%' . $numericId . '%')
            ->first();
        
        if ($service) {
            return $service->service_id;
        }

        // If not found, generate a new UUID and log the issue
        \Log::error('Could not find UUID for numeric ID', [
            'numeric_id' => $numericId
        ]);
        
        return Str::uuid()->toString();
    }

    /**
     * Safely execute whereIn query with UUID values
     */
    public static function whereInUuid($query, $column, $values)
    {
        if (empty($values)) {
            return $query->whereRaw('1 = 0'); // Return no results
        }

        // Cast all values to proper UUIDs
        $uuidValues = array_filter(array_map([self::class, 'castToUuid'], (array) $values));
        
        if (empty($uuidValues)) {
            return $query->whereRaw('1 = 0'); // Return no results
        }

        // Use proper UUID casting in the query
        return $query->whereRaw("{$column}::text IN (" . implode(',', array_fill(0, count($uuidValues), '?')) . ")", $uuidValues);
    }

    /**
     * Safely execute where query with UUID value
     */
    public static function whereUuid($query, $column, $operator, $value = null)
    {
        // Handle the case where operator is omitted
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $uuidValue = self::castToUuid($value);
        
        if (!$uuidValue) {
            return $query->whereRaw('1 = 0'); // Return no results
        }

        return $query->whereRaw("{$column}::text {$operator} ?", [$uuidValue]);
    }

    /**
     * Generate a new UUID
     */
    public static function generate()
    {
        return Str::uuid()->toString();
    }

    /**
     * Validate and sanitize UUID input from requests
     */
    public static function validateRequestUuid($value, $fieldName = 'id')
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("The {$fieldName} field is required.");
        }

        if (!self::isValidUuid($value)) {
            // Try to cast it
            $castedValue = self::castToUuid($value);
            if (!self::isValidUuid($castedValue)) {
                throw new \InvalidArgumentException("The {$fieldName} field must be a valid UUID.");
            }
            return $castedValue;
        }

        return $value;
    }

    /**
     * Convert array of mixed IDs to UUIDs
     */
    public static function convertArrayToUuids(array $ids)
    {
        return array_filter(array_map([self::class, 'castToUuid'], $ids));
    }

    /**
     * Safe UUID comparison
     */
    public static function compareUuids($uuid1, $uuid2)
    {
        $uuid1 = self::castToUuid($uuid1);
        $uuid2 = self::castToUuid($uuid2);
        
        return $uuid1 === $uuid2;
    }
}
