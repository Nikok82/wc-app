<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    protected $table = 'awc_flags';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
