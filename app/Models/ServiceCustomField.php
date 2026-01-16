<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceCustomField extends Model
{
    use HasFactory;

    protected $table = 'service_custom_fields';
    protected $primaryKey = 'field_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'field_label',
        'placeholder',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'field_id' => 'string',
            'service_id' => 'string',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->field_id) {
                $model->field_id = Str::uuid()->toString();
            }
        });
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }
}
