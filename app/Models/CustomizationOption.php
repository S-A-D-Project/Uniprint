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
        'product_id',
        'option_name',
        'option_type',
        'price_modifier',
    ];

    protected function casts(): array
    {
        return [
            'option_id' => 'string',
            'product_id' => 'string',
            'price_modifier' => 'decimal:2',
        ];
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function orderItemCustomizations()
    {
        return $this->hasMany(OrderItemCustomization::class, 'option_id', 'option_id');
    }
}
