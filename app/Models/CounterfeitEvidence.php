<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounterfeitEvidence extends Model
{
    protected $table = 'counterfeit_evidence';

    protected $fillable = [
        'report_id', 'file_path', 'file_type', 'sha256_hash', 'evidence_type', 'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(CounterfeitReport::class, 'report_id');
    }
}
