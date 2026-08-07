<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentStage extends Model
{
    protected $table = 'awc_tournament_stages';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
