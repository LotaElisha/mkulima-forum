<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Crop extends Model
{
    protected $fillable = [
        'uuid', 'name', 'slug', 'swahili_name', 'category', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($crop) {
            if (empty($crop->uuid)) {
                $crop->uuid = (string) Str::uuid();
            }
            if (empty($crop->slug)) {
                $crop->slug = Str::slug($crop->name);
            }
        });
    }
}
