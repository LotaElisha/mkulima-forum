<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityChannelModerator extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel_id', 'user_id', 'role', 'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(CommunityChannel::class, 'channel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
