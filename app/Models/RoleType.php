<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleType extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_type_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'role_type_id',
        'user_role_type',
        'description',
    ];

    public function roles()
    {
        return $this->hasMany(Role::class, 'role_type_id', 'role_type_id');
    }
}
