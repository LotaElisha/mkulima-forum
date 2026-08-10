<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Topic extends Model
{
    protected $table = 'agricultural_topics';

    protected $fillable = [
        'uuid', 'name', 'slug', 'swahili_name', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($topic) {
            if (empty($topic->uuid)) {
                $topic->uuid = (string) Str::uuid();
            }
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }
}
