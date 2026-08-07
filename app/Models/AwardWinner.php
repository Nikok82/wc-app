<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardWinner extends Model
{
    protected $table = 'awc_award_winners';
    protected $primaryKey = 'key_id';
    public $timestamps = false;
    protected $guarded = ['key_id'];
}
