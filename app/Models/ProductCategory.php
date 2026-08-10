<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $fillable = [
        'uuid', 'name', 'slug', 'swahili_name', 'code', 'requires_certification',
    ];

    protected $casts = [
        'requires_certification' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($cat) {
            if (empty($cat->uuid)) {
                $cat->uuid = (string) Str::uuid();
            }
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }
}
