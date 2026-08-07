<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotQualifiedTeam extends Model
{
    protected $table = 'awc_not_qualified_teams';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
