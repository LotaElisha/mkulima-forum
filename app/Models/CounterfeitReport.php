<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CounterfeitReport extends Model
{
    protected $fillable = [
        'uuid', 'case_number', 'reporter_id', 'product_id', 'product_name',
        'product_category_id', 'serial_number', 'batch_number', 'dealer_id',
        'dealer_name_raw', 'purchase_date', 'geo_unit_id', 'description',
        'crop_affected_id', 'status', 'contact_preference', 'reporter_anonymous',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'reporter_anonymous' => 'boolean',
    ];

    public const STATUSES = [
        'RECEIVED', 'UNDER_REVIEW', 'ESCALATED', 'RESOLVED', 'DISMISSED',
    ];

    protected static function booted(): void
    {
        static::creating(function ($report) {
            if (empty($report->uuid)) {
                $report->uuid = (string) Str::uuid();
            }
            if (empty($report->case_number)) {
                $report->case_number = 'MF-CF-'.date('Y').'-'.Str::padLeft(mt_rand(1, 999999), 6, '0');
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function product()
    {
        return $this->belongsTo(RegulatedProduct::class, 'product_id');
    }

    public function dealer()
    {
        return $this->belongsTo(Agrodealer::class, 'dealer_id');
    }

    public function geoUnit()
    {
        return $this->belongsTo(GeoUnit::class, 'geo_unit_id');
    }

    public function evidence()
    {
        return $this->hasMany(CounterfeitEvidence::class, 'report_id');
    }

    public function regulatoryCase()
    {
        return $this->hasOne(RegulatoryCase::class, 'report_id');
    }
}
