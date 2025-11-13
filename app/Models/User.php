<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'position',
        'department',
        'username',
        'password_hash',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relationships
    public function role()
    {
        return $this->hasOne(Role::class, 'user_id', 'user_id');
    }

    // Get user role type without accessor to avoid circular reference
    public function getUserRoleType()
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role.roleType');
        }
        
        return $this->role && $this->role->roleType ? $this->role->roleType->user_role_type : null;
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'user_id');
    }

    public function designAssets()
    {
        return $this->hasMany(DesignAsset::class, 'user_id', 'user_id');
    }

    public function customerOrders()
    {
        return $this->hasMany(CustomerOrder::class, 'customer_account_id', 'user_id');
    }

    public function aiImageGenerations()
    {
        return $this->hasMany(AiImageGeneration::class, 'user_id', 'user_id');
    }

    public function chatbotInteractions()
    {
        return $this->hasMany(ChatbotInteraction::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->getUserRoleType() === 'admin';
    }

    public function isBusinessUser()
    {
        return $this->getUserRoleType() === 'business_user';
    }

    public function isCustomer()
    {
        return $this->getUserRoleType() === 'customer';
    }
}
