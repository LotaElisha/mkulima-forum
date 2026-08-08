<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\BelongsToTenant;

class AiProvider extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'name',
        'provider_type',
        'base_url',
        'model',
        'status',
        'is_default',
        'temperature',
        'max_tokens',
        'timeout',
        'organization_id',
        'project_id',
        'rate_limit',
        'additional_config',
        'last_tested_at',
        'last_connection_status',
        'last_connection_error',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'timeout' => 'integer',
        'rate_limit' => 'integer',
        'additional_config' => 'array',
        'last_tested_at' => 'datetime',
    ];

    protected $appends = ['masked_api_key', 'has_api_key'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($provider) {
            if (empty($provider->uuid)) {
                $provider->uuid = (string) Str::uuid();
            }
        });
    }

    public function credential()
    {
        return $this->hasOne(AiProviderCredential::class, 'ai_provider_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function featureRoutes()
    {
        return $this->hasMany(AiFeatureRoute::class, 'ai_provider_id');
    }

    public function usageLogs()
    {
        return $this->hasMany(AiUsageLog::class, 'ai_provider_id');
    }

    public function getHasApiKeyAttribute(): bool
    {
        return $this->credential !== null;
    }

    public function getMaskedApiKeyAttribute(): ?string
    {
        if (!$this->credential) {
            return null;
        }

        try {
            $secretManager = app(\App\Services\AI\Secrets\SecretManagerServiceInterface::class);
            $plainKey = $secretManager->getSecret($this->id);
            if (empty($plainKey)) {
                return null;
            }

            $length = strlen($plainKey);
            if ($length <= 8) {
                return '••••••••';
            }

            $prefix = substr($plainKey, 0, 4);
            $suffix = substr($plainKey, -3);
            return $prefix . '••••••••' . $suffix;
        } catch (\Throwable $e) {
            return '••••••••';
        }
    }
}
