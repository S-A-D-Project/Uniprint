<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_account_id',
        'enterprise_id',
        'order_creation_date',
        'total_order_amount',
        'current_status',
    ];

    protected function casts(): array
    {
        return [
            'order_creation_date' => 'datetime',
            'total_order_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_account_id', 'user_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'enterprise_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'order_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'order_id', 'order_id');
    }
}
