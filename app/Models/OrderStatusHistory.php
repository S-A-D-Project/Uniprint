<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';
    protected $primaryKey = 'approval_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'approval_id',
        'purchase_order_id',
        'user_id',
        'status_id',
        'remarks',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}
