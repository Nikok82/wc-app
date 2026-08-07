<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerAppearance extends Model
{
    protected $table = 'awc_player_appearances';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
    protected $casts = [
        'match_date' => 'date',
    ];
}
