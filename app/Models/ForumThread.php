<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ForumThread extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'forum_category_id',
        'user_id',
        'uuid',
        'title',
        'body',
        'media',
        'language',
        'is_pinned',
        'is_locked',
        'is_verified_answer',
        'view_count',
        'reply_count',
        'upvote_count',
        'status',
    ];

    protected $casts = [
        'media' => 'array',
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_verified_answer' => 'boolean',
        'view_count' => 'integer',
        'reply_count' => 'integer',
        'upvote_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($thread) {
            if (empty($thread->uuid)) {
                $thread->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class);
    }
}
