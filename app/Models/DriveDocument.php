<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriveDocument extends Model
{
    protected $fillable = [
        'google_file_id',
        'name',
        'mime_type',
        'size',
        'web_view_link',
        'web_content_link',
        'icon_link',
        'thumbnail_link',
        'drive_modified_at',
        'synced_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'drive_modified_at' => 'datetime',
        'synced_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}
