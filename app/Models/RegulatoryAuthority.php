<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegulatoryAuthority extends Model
{
    protected $fillable = [
        'uuid', 'name', 'acronym', 'country', 'product_categories', 'is_active', 'display_note',
    ];

    protected $casts = [
        'product_categories' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($auth) {
            if (empty($auth->uuid)) {
                $auth->uuid = (string) Str::uuid();
            }
        });
    }

    public function dataSources()
    {
        return $this->hasMany(RegulatoryDataSource::class, 'authority_id');
    }

    public function products()
    {
        return $this->hasMany(RegulatedProduct::class, 'authority_id');
    }
}
