<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BotConversation extends Model
{
    protected $fillable = ['tenant_id', 'user_id', 'uuid', 'title', 'language'];

    protected static function booted(): void
    {
        static::creating(function ($conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(BotMessage::class)->orderBy('id');
    }
}
