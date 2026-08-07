<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Confederation extends Model
{
    protected $table = 'awc_confederations';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
