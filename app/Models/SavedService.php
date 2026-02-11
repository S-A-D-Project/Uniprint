<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavedService extends Model
{
    use HasFactory;

    protected $primaryKey = 'saved_service_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'service_id',
        'quantity',
        'customizations',
        'custom_fields',
        'special_instructions',
        'unit_price',
        'total_price',
        'saved_at',
    ];

    protected $casts = [
        'saved_service_id' => 'string',
        'user_id' => 'string',
        'service_id' => 'string',
        'customizations' => 'array',
        'custom_fields' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'saved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->saved_service_id) {
                $model->saved_service_id = Str::uuid()->toString();
            }
            if (!$model->saved_at) {
                $model->saved_at = now();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    // Backward compatibility
    public function product()
    {
        return $this->service();
    }

    public function customizationOptions()
    {
        return $this->belongsToMany(
            CustomizationOption::class,
            'saved_service_customizations',
            'saved_service_id',
            'option_id'
        )->withPivot('quantity');
    }

    // Accessors & Mutators
    public function getFormattedUnitPriceAttribute()
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '₱' . number_format($this->total_price, 2);
    }

    public function getCustomizationCostAttribute()
    {
        return $this->customizationOptions->sum('price_modifier');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('saved_at', '>=', now()->subDays($days));
    }

    // Static Methods

    /**
     * Save a service for user
     */
    public static function saveService($userId, $serviceId, $quantity = 1, $customizations = [], $customFields = [], $specialInstructions = null)
    {
        $service = Service::with('customFields')->findOrFail($serviceId);

        $customizations = is_array($customizations) ? array_values(array_unique($customizations)) : [];
        sort($customizations);

        $customFields = is_array($customFields) ? $customFields : [];
        $customFields = array_filter($customFields, fn($v) => $v !== null);
        foreach ($customFields as $k => $v) {
            if (!is_string($k)) {
                unset($customFields[$k]);
                continue;
            }
            $customFields[$k] = is_string($v) ? trim($v) : '';
            if ($customFields[$k] === '') {
                unset($customFields[$k]);
            }
        }
        ksort($customFields);

        $missing = [];
        foreach ($service->customFields->where('is_required', true) as $field) {
            if (empty($customFields[$field->field_id] ?? null)) {
                $missing[] = $field->field_label;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException('Please fill in required fields: ' . implode(', ', $missing));
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('services', 'supports_custom_size') && !empty($service->supports_custom_size)) {
            $customSizeOptionId = DB::table('customization_options')
                ->where('service_id', $serviceId)
                ->whereRaw('LOWER(option_type) = ?', ['size'])
                ->whereRaw('LOWER(option_name) = ?', ['custom size'])
                ->value('option_id');

            $customSizeSelected = $customSizeOptionId && in_array((string) $customSizeOptionId, $customizations, true);

            if ($customSizeSelected) {
                $wRaw = $customFields['custom_size_width'] ?? null;
                $hRaw = $customFields['custom_size_height'] ?? null;

                if ($wRaw === null || $hRaw === null) {
                    throw new \InvalidArgumentException('Please enter both custom width and height.');
                }

                $w = (float) $wRaw;
                $h = (float) $hRaw;

                if ($w <= 0 || $h <= 0) {
                    throw new \InvalidArgumentException('Custom width and height must be greater than 0.');
                }

                $minW = \Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_min_width') ? (float) ($service->custom_size_min_width ?? 0) : 0;
                $maxW = \Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_max_width') ? (float) ($service->custom_size_max_width ?? 0) : 0;
                $minH = \Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_min_height') ? (float) ($service->custom_size_min_height ?? 0) : 0;
                $maxH = \Illuminate\Support\Facades\Schema::hasColumn('services', 'custom_size_max_height') ? (float) ($service->custom_size_max_height ?? 0) : 0;

                if (($minW > 0 && $w < $minW) || ($maxW > 0 && $w > $maxW) || ($minH > 0 && $h < $minH) || ($maxH > 0 && $h > $maxH)) {
                    throw new \InvalidArgumentException('Custom size is out of the allowed range.');
                }
            } else {
                unset($customFields['custom_size_width'], $customFields['custom_size_height']);
            }
        }
        
        // Calculate price including customizations
        $unitPrice = $service->base_price;
        $customizationCost = 0;
        
        if (!empty($customizations)) {
            $customizationOptions = CustomizationOption::whereIn('option_id', $customizations)->get();
            $customizationCost = $customizationOptions->sum('price_modifier');
        }
        
        $unitPrice += $customizationCost;
        $totalPrice = $unitPrice * $quantity;
        
        // Check if service already exists with same customizations
        $existingQuery = static::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->where('special_instructions', $specialInstructions);

        $existingQuery->whereRaw("COALESCE(custom_fields, '{}'::jsonb) = ?::jsonb", [json_encode($customFields)]);

        if (empty($customizations)) {
            $existingQuery->where(function ($q) {
                $q->whereNull('customizations')
                    ->orWhereJsonLength('customizations', 0);
            });
        } else {
            foreach ($customizations as $optionId) {
                $existingQuery->whereJsonContains('customizations', $optionId);
            }
            $existingQuery->whereJsonLength('customizations', count($customizations));
        }

        $existingService = $existingQuery->first();
        
        if ($existingService) {
            // Update existing service
            $existingService->update([
                'quantity' => $existingService->quantity + $quantity,
                'total_price' => ($existingService->quantity + $quantity) * $unitPrice,
            ]);
            
            // Update customizations relationship
            $existingService->customizationOptions()->sync($customizations);
            
            return $existingService;
        } else {
            // Create new saved service
            $savedService = static::create([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'quantity' => $quantity,
                'customizations' => $customizations,
                'custom_fields' => $customFields,
                'special_instructions' => $specialInstructions,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);
            
            // Attach customizations
            if (!empty($customizations)) {
                $savedService->customizationOptions()->attach($customizations);
            }
            
            return $savedService;
        }
    }

    /**
     * Remove saved service
     */
    public static function removeService($userId, $savedServiceId)
    {
        return static::where('user_id', $userId)
            ->where('saved_service_id', $savedServiceId)
            ->delete();
    }

    /**
     * Update service quantity
     */
    public static function updateServiceQuantity($userId, $savedServiceId, $quantity)
    {
        $service = static::where('user_id', $userId)
            ->where('saved_service_id', $savedServiceId)
            ->first();
        
        if ($service) {
            if ($quantity <= 0) {
                static::removeService($userId, $savedServiceId);
                return null;
            }
            
            $service->update([
                'quantity' => $quantity,
                'total_price' => $service->unit_price * $quantity,
            ]);
            
            return $service;
        }
        
        return false;
    }

    /**
     * Clear all saved services for user
     */
    public static function clearServices($userId)
    {
        return static::where('user_id', $userId)->delete();
    }

    /**
     * Get saved services count for user
     */
    public static function getServicesCount($userId)
    {
        return static::where('user_id', $userId)->sum('quantity');
    }

    /**
     * Get total amount for user's saved services
     */
    public static function getTotalAmount($userId)
    {
        return static::where('user_id', $userId)->sum('total_price');
    }

    /**
     * Get subtotal for user's saved services
     */
    public static function getSubtotal($userId)
    {
        return static::getTotalAmount($userId);
    }

    /**
     * Get user's saved services with relationships
     */
    public static function getUserServices($userId)
    {
        return static::where('user_id', $userId)
            ->with(['service.enterprise', 'customizationOptions'])
            ->orderBy('saved_at', 'desc')
            ->get();
    }
}
