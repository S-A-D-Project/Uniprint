<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * SafePropertyException
 * 
 * Custom exception for handling undefined property access errors
 */
class SafePropertyException extends Exception
{
    protected $object;
    protected $property;
    protected $context;

    public function __construct($object, string $property, array $context = [], string $message = null)
    {
        $this->object = $object;
        $this->property = $property;
        $this->context = $context;

        $message = $message ?: "Undefined property: " . get_class($object) . "::\${$property}";
        
        parent::__construct($message);
    }

    /**
     * Report the exception
     */
    public function report()
    {
        Log::warning('Undefined property access prevented', [
            'object_class' => is_object($this->object) ? get_class($this->object) : gettype($this->object),
            'property' => $this->property,
            'context' => $this->context,
            'trace' => $this->getTraceAsString()
        ]);
    }

    /**
     * Get the object that caused the exception
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get the property that was accessed
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * Get additional context
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
