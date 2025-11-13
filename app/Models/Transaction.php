<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_reference_id',
        'payment_date_time',
        'amount_paid',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'payment_date_time' => 'datetime',
            'amount_paid' => 'decimal:2',
            'is_verified' => 'boolean',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }
}
