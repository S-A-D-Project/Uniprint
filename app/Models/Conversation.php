<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $primaryKey = 'conversation_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'customer_id',
        'business_id',
        'subject',
        'status',
        'initiated_by',
        'initiated_at',
        'last_message_at',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->conversation_id)) {
                $model->conversation_id = (string) Str::uuid();
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }

    public function business()
    {
        return $this->belongsTo(User::class, 'business_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id', 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id', 'conversation_id')
            ->latestOfMany();
    }

    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function getOtherParticipant($currentUserId)
    {
        return $this->customer_id === $currentUserId 
            ? $this->business 
            : $this->customer;
    }
}
