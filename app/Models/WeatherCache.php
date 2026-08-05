<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherCache extends Model
{
    use HasFactory;

    protected $table = 'weather_cache';

    protected $fillable = [
        'location',
        'lat',
        'lon',
        'current_data',
        'forecast_data',
        'advisory_data',
        'expires_at',
    ];

    protected $casts = [
        'current_data' => 'array',
        'forecast_data' => 'array',
        'advisory_data' => 'array',
        'expires_at' => 'datetime',
        'lat' => 'decimal:6',
        'lon' => 'decimal:6',
    ];
}
