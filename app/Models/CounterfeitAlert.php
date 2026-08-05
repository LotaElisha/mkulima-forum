<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CounterfeitAlert extends Model
{
    protected $fillable = [
        'uuid',
        'reporter_id',
        'product_name',
        'product_type',
        'registration_number',
        'batch_number',
        'dealer_name',
        'region',
        'district',
        'description',
        'photo_path',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($alert) {
            if (empty($alert->uuid)) {
                $alert->uuid = (string) Str::uuid();
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
