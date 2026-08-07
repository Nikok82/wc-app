<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentStanding extends Model
{
    protected $table = 'awc_tournament_standings';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
