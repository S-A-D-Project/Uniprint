<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SystemFeedback extends Model
{
    use HasFactory;

    protected $table = 'system_feedback';
    protected $primaryKey = 'feedback_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'feedback_id',
        'user_id',
        'category',
        'rating',
        'subject',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'feedback_id' => 'string',
        'user_id' => 'string',
        'reviewed_by' => 'string',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->feedback_id)) {
                $model->feedback_id = (string) Str::uuid();
            }
        });
    }
}
