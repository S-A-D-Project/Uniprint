<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\SendsTwoFactorCode;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SendsTwoFactorCode;

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
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
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
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_attempts' => 'integer',
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

    // OTP Helper Methods
    public function generateOtp(): string
    {
        $otp = (string) random_int(100000, 999999);
        
        $this->forceFill([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_attempts' => 0,
        ])->save();
        
        return $otp;
    }

    public function isValidOtp(string $otp): bool
    {
        if (empty($this->otp_code) || empty($this->otp_expires_at)) {
            return false;
        }

        if (now()->greaterThan($this->otp_expires_at)) {
            return false;
        }

        return hash_equals($this->otp_code, $otp);
    }

    public function hasValidOtp(): bool
    {
        if (empty($this->otp_code) || empty($this->otp_expires_at)) {
            return false;
        }

        return now()->lessThan($this->otp_expires_at);
    }

    public function incrementOtpAttempts(): void
    {
        $this->increment('otp_attempts');
    }

    public function hasExceededOtpAttempts(int $maxAttempts = 3): bool
    {
        return $this->otp_attempts >= $maxAttempts;
    }

    public function clearOtp(): void
    {
        $this->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ])->save();
    }
}
