<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotInteraction extends Model
{
    use HasFactory;

    protected $primaryKey = 'interaction_id';

    protected $fillable = [
        'user_id',
        'session_id',
        'message_text',
        'is_from_user',
        'interaction_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'is_from_user' => 'boolean',
            'interaction_timestamp' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
