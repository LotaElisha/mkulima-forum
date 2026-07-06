<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ForumVote extends Model
{
    protected $fillable = ['user_id', 'votable_type', 'votable_id'];

    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
