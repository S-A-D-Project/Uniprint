<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'staff_name',
        'position',
        'department',
        'user_id',
        'enterprise_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'enterprise_id');
    }

    public function orderStatusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class, 'staff_id', 'staff_id');
    }
}
