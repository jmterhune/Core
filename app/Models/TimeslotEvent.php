<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeslotEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "timeslot_events";
    protected $fillable = ['event_id','timeslot_id'];

}
