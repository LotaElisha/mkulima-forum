<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulatoryCase extends Model
{
    protected $fillable = [
        'report_id', 'case_number', 'escalation_mode', 'authority_id',
        'status', 'case_file_pdf_path', 'case_file_json_path', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(CounterfeitReport::class, 'report_id');
    }

    public function authority()
    {
        return $this->belongsTo(RegulatoryAuthority::class, 'authority_id');
    }
}
