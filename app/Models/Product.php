<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuidFields;
use App\Utils\UuidHelper;

class Product extends Model
{
    use HasFactory, HasUuidFields;

    protected $primaryKey = 'product_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'enterprise_id',
        'product_name',
        'description',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'string',
            'enterprise_id' => 'string',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'enterprise_id');
    }

    public function customizationOptions()
    {
        return $this->hasMany(CustomizationOption::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    /**
     * Get the UUID fields for this model
     */
    protected function getUuidFields()
    {
        return [
            'product_id',
            'enterprise_id',
        ];
    }

    /**
     * Scope to find products by enterprise IDs with proper UUID handling
     */
    public function scopeWhereEnterpriseIn($query, $enterpriseIds)
    {
        return UuidHelper::whereInUuid($query, 'enterprise_id', $enterpriseIds);
    }
}
