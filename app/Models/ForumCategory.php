<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ForumCategory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'requires_expert',
        'is_active',
    ];

    protected $casts = [
        'requires_expert' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function threads()
    {
        return $this->hasMany(ForumThread::class);
    }
}
