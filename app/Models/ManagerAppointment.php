<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerAppointment extends Model
{
    protected $table = 'awc_manager_appointments';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
