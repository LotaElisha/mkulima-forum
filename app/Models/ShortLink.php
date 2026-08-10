<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    protected $fillable = [
        'slug', 'target_url', 'link_type', 'subject_type', 'subject_id',
        'click_count', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'click_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($link) {
            if (empty($link->slug)) {
                $link->slug = Str::random(8);
            }
        });
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
