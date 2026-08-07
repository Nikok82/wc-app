<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultForYear extends Model
{
    protected $table = 'awc_results_for_year';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
