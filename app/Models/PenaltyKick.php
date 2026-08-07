<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyKick extends Model
{
    protected $table = 'awc_penalty_kicks';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
    protected $casts = [
        'match_date' => 'date',
    ];
}
