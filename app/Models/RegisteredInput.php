<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegisteredInput extends Model
{
    public const TYPES = [
        'pesticide', 'herbicide', 'fungicide', 'insecticide',
        'fertilizer', 'vet_product', 'seed',
    ];

    public const STATUSES = ['registered', 'banned', 'withdrawn'];

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'registration_number',
        'manufacturer',
        'distributor',
        'status',
        'source',
        'source_date',
    ];

    protected $casts = [
        'source_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($input) {
            if (empty($input->uuid)) {
                $input->uuid = (string) Str::uuid();
            }
        });
    }
}
