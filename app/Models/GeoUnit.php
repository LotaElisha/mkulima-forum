<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GeoUnit extends Model
{
    protected $fillable = [
        'uuid', 'type', 'name', 'code', 'parent_id', 'latitude', 'longitude', 'boundary_geojson',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'boundary_geojson' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($unit) {
            if (empty($unit->uuid)) {
                $unit->uuid = (string) Str::uuid();
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(GeoUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(GeoUnit::class, 'parent_id');
    }
}
