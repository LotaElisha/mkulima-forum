<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityChannelClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel_id', 'actor_id', 'anon_id', 'event', 'referrer', 'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(CommunityChannel::class, 'channel_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
