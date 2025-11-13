<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizationGroup extends Model
{
    use HasFactory;

    protected $primaryKey = 'group_id';

    protected $fillable = [
        'product_id',
        'group_name',
        'group_type',
        'is_required',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function customizationOptions()
    {
        return $this->hasMany(CustomizationOption::class, 'group_id', 'group_id');
    }
}
