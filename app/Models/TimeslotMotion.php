<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeslotMotion extends Model
{
    use HasFactory;

    protected $table = 'timeslot_motions';
    protected $fillable = ['timeslotable_id','timeslotable_type','motion_id'];
    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableAttribute()
    {
        return $this->motion;
    }

    public function motion(){
        return $this->hasOne(Motion::class,'id','motion_id');
    }
}
