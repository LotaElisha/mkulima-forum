<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ForumReply extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'forum_thread_id',
        'user_id',
        'parent_id',
        'body',
        'media',
        'is_expert_answer',
        'upvote_count',
        'status',
    ];

    protected $casts = [
        'media' => 'array',
        'is_expert_answer' => 'boolean',
        'upvote_count' => 'integer',
    ];

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
