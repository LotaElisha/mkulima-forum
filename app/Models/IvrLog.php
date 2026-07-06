<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvrLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'phone',
        'input',
        'action',
        'status',
        'duration',
    ];

    protected $casts = [
        'duration' => 'integer',
        'created_at' => 'datetime',
    ];
}
