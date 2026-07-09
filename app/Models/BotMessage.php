<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMessage extends Model
{
    protected $fillable = ['bot_conversation_id', 'role', 'content', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(BotConversation::class, 'bot_conversation_id');
    }
}
