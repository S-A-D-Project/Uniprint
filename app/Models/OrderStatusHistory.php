<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_history';
    protected $primaryKey = 'history_id';

    protected $fillable = [
        'order_id',
        'status_name',
        'status_timestamp',
        'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'status_timestamp' => 'datetime',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
