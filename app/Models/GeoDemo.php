<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoDemo extends Model
{
    protected $table = 'awc_geo_demo';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
