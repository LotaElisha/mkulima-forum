<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ConfigSetting extends Model
{
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'is_encrypted', 'version', 'updated_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'version' => 'integer',
    ];

    protected $hidden = [];

    public function getValueAttribute($val)
    {
        if (empty($val)) {
            return null;
        }

        if ($this->is_encrypted || $this->type === 'secret') {
            try {
                $val = Crypt::decryptString($val);
            } catch (\Throwable $e) {
                // Return as is if decryption fails
            }
        }

        return match ($this->type) {
            'integer' => (int) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($val, true),
            default => $val,
        };
    }

    public function setValueAttribute($val)
    {
        if (is_array($val) || is_object($val)) {
            $val = json_encode($val);
        }

        if ($this->is_encrypted || $this->type === 'secret') {
            $val = Crypt::encryptString((string) $val);
        }

        $this->attributes['value'] = $val;
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
