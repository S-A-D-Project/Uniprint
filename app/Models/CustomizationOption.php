<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizationOption extends Model
{
    use HasFactory;

    protected $primaryKey = 'option_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'option_name',
        'option_type',
        'price_modifier',
    ];

    protected function casts(): array
    {
        return [
            'option_id' => 'string',
            'service_id' => 'string',
            'price_modifier' => 'decimal:2',
        ];
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    // Backward compatibility alias
    public function product()
    {
        return $this->service();
    }

    public function orderItemCustomizations()
    {
        return $this->hasMany(OrderItemCustomization::class, 'option_id', 'option_id');
    }
}
