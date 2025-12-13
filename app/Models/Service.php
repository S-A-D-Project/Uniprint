<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuidFields;
use App\Utils\UuidHelper;

class Service extends Model
{
    use HasFactory, HasUuidFields;

    protected $table = 'services';
    protected $primaryKey = 'service_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'enterprise_id',
        'service_name',
        'description',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'service_id' => 'string',
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
        return $this->hasMany(CustomizationOption::class, 'service_id', 'service_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'service_id', 'service_id');
    }

    /**
     * Get the UUID fields for this model
     */
    protected function getUuidFields()
    {
        return [
            'service_id',
            'enterprise_id',
        ];
    }

    /**
     * Scope to find services by enterprise IDs with proper UUID handling
     */
    public function scopeWhereEnterpriseIn($query, $enterpriseIds)
    {
        return UuidHelper::whereInUuid($query, 'enterprise_id', $enterpriseIds);
    }
}
