<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiseaseScan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'uuid',
        'image_path',
        'disease_name',
        'confidence_score',
        'description',
        'treatment_recommendation',
        'affected_areas',
        'scan_source',
        'status',
        'gemini_response',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'affected_areas' => 'array',
        'gemini_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($scan) {
            if (empty($scan->uuid)) {
                $scan->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
