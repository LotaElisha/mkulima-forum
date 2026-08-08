<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiProviderCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_provider_id',
        'encrypted_api_key',
        'key_hash',
    ];

    protected $hidden = [
        'encrypted_api_key', // Never serialize raw encrypted string to API JSON
    ];

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
