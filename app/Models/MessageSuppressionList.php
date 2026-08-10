<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageSuppressionList extends Model
{
    protected $table = 'message_suppression_list';

    protected $fillable = [
        'recipient_identifier', 'channel_driver', 'reason',
    ];
}
