<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefereeAppointment extends Model
{
    protected $table = 'awc_referee_appointments';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
