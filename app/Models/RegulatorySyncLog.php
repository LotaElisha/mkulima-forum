<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulatorySyncLog extends Model
{
    protected $fillable = [
        'source_id', 'started_at', 'completed_at', 'rows_imported',
        'rows_updated', 'rows_failed', 'error_message', 'diff_summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'diff_summary' => 'array',
    ];

    public function dataSource()
    {
        return $this->belongsTo(RegulatoryDataSource::class, 'source_id');
    }
}
