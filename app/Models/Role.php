<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'role_id',
        'user_id',
        'role_type_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function roleType()
    {
        return $this->belongsTo(RoleType::class, 'role_type_id', 'role_type_id');
    }
}
