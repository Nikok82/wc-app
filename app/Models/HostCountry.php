<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostCountry extends Model
{
    protected $table = 'awc_host_countries';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
