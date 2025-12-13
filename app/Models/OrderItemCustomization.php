<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemCustomization extends Model
{
    use HasFactory;

    protected $primaryKey = 'customization_id';

    protected $fillable = [
        'order_item_id',
        'option_id',
        'option_price_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'option_price_snapshot' => 'decimal:2',
        ];
    }

    // Relationships
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'item_id');
    }

    public function option()
    {
        return $this->belongsTo(CustomizationOption::class, 'option_id', 'option_id');
    }
}
