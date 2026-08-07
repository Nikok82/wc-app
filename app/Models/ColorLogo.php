<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorLogo extends Model
{
    protected $table = 'awc_colors_logos';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
