<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'order_id',
        'service_id',
        'quantity',
        'item_subtotal',
        'notes_to_enterprise',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'item_subtotal' => 'decimal:2',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    // Backward compatibility alias
    public function product()
    {
        return $this->service();
    }

    public function customizations()
    {
        return $this->hasMany(OrderItemCustomization::class, 'order_item_id', 'item_id');
    }
}
