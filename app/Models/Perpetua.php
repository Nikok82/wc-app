<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perpetua extends Model
{
    protected $table = 'awc_perpetua';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
