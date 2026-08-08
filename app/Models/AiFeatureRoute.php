<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AiFeatureRoute extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'feature_key',
        'ai_provider_id',
        'model_override',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
