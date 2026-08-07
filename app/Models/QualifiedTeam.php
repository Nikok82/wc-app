<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualifiedTeam extends Model
{
    protected $table = 'awc_qualified_teams';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
