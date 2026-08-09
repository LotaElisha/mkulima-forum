<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FarmActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'farm_id',
        'activity_type',
        'activity_date',
        'cost_tzs',
        'notes',
    ];

    protected $casts = [
        'cost_tzs' => 'float',
        'activity_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
