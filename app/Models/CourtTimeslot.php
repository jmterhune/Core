<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtTimeslot extends Model
{
    use HasFactory;

    protected $table = 'court_timeslots';
    protected $fillable = ['court_id','timeslot_id'];

    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableAttribute()
    {
        return 'CourtTimeslot';
    }

}
