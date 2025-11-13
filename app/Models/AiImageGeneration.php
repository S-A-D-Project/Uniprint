<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiImageGeneration extends Model
{
    use HasFactory;

    protected $primaryKey = 'generation_id';

    protected $fillable = [
        'user_id',
        'prompt_text',
        'generated_image_url',
        'generation_timestamp',
        'related_asset_id',
    ];

    protected function casts(): array
    {
        return [
            'generation_timestamp' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function relatedAsset()
    {
        return $this->belongsTo(DesignAsset::class, 'related_asset_id', 'asset_id');
    }
}
