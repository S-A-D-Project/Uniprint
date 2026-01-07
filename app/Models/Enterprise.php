<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuidFields;
use App\Utils\UuidHelper;

class Enterprise extends Model
{
    use HasFactory, HasUuidFields;

    protected $primaryKey = 'enterprise_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'shop_logo',
        'address',
        'contact_person',
        'contact_number',
        'tin_no',
    ];

    protected function casts(): array
    {
        return [
            'enterprise_id' => 'string',
        ];
    }

    // Accessors for backward compatibility
    public function getIsActiveAttribute()
    {
        if (array_key_exists('is_active', $this->attributes)) {
            return (bool) $this->attributes['is_active'];
        }

        return true;
    }

    public function getCategoryAttribute()
    {
        if (array_key_exists('category', $this->attributes)) {
            return $this->attributes['category'];
        }

        return 'Printing Services';
    }

    public function getEmailAttribute()
    {
        if (array_key_exists('email', $this->attributes)) {
            return $this->attributes['email'];
        }

        return null;
    }

    // Relationships
    public function staff()
    {
        return $this->hasMany(Staff::class, 'enterprise_id', 'enterprise_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'enterprise_id', 'enterprise_id');
    }

    // Backward compatibility alias
    public function products()
    {
        return $this->services();
    }

    public function customerOrders()
    {
        return $this->hasMany(CustomerOrder::class, 'enterprise_id', 'enterprise_id');
    }
}
