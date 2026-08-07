<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    // Nota: la tabella si chiama awc_matches ma il model NON puo' chiamarsi
    // 'Match' perche' e' una parola riservata in PHP. Usiamo 'GameMatch'.
    protected $table = 'awc_matches';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
    protected $casts = [
        'match_date' => 'date',
    ];
}
