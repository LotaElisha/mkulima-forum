<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    public const REASONS = ['spam', 'misleading', 'fraud', 'abuse', 'counterfeit', 'other'];

    /** Maps API type slugs to model classes. */
    public const TYPES = [
        'forum_thread' => ForumThread::class,
        'forum_reply' => ForumReply::class,
        'product' => Product::class,
        'user' => User::class,
    ];

    protected $fillable = [
        'uuid',
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'status',
        'resolved_by',
        'resolution_action',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($report) {
            if (empty($report->uuid)) {
                $report->uuid = (string) Str::uuid();
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reportable()
    {
        $class = self::TYPES[$this->reportable_type] ?? null;

        return $class ? $class::find($this->reportable_id) : null;
    }
}
