<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class KbDocument extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'kb_documents';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'content',
        'source',
        'category',
        'language',
        'metadata',
        'is_verified',
        'published_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_verified' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($doc) {
            if (empty($doc->uuid)) {
                $doc->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
