<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Manufacturer extends Model
{
    protected $fillable = [
        'uuid', 'name', 'country', 'registration_number', 'is_verified', 'provenance',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) {
                $m->uuid = (string) Str::uuid();
            }
            if (empty($m->provenance)) {
                $m->provenance = 'PLATFORM';
            }
        });
    }

    public function products()
    {
        return $this->hasMany(RegulatedProduct::class, 'manufacturer_id');
    }
}
