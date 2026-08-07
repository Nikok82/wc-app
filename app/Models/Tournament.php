<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'awc_tournaments';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
