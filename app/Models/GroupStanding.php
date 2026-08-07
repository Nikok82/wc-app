<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupStanding extends Model
{
    protected $table = 'awc_group_standings';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
