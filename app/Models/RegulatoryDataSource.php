<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class RegulatoryDataSource extends Model
{
    protected $fillable = [
        'authority_id', 'name', 'source_url', 'api_endpoint', 'api_key_encrypted',
        'auth_type', 'backing_mode', 'sync_interval_minutes', 'is_active',
        'last_synced_at', 'data_version', 'confidence_level',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'confidence_level' => 'integer',
        'sync_interval_minutes' => 'integer',
    ];

    public function authority()
    {
        return $this->belongsTo(RegulatoryAuthority::class, 'authority_id');
    }

    public function setApiKeyEncryptedAttribute($val)
    {
        if (! empty($val)) {
            $this->attributes['api_key_encrypted'] = Crypt::encryptString($val);
        } else {
            $this->attributes['api_key_encrypted'] = null;
        }
    }

    public function getApiKeyEncryptedAttribute($val)
    {
        if (empty($val)) {
            return null;
        }
        try {
            return Crypt::decryptString($val);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
