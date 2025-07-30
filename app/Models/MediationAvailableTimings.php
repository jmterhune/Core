<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Http\Traits\CustomRevisionableTrait;
use Illuminate\Database\Eloquent\Model;

class MediationAvailableTimings extends Model
{
    use CustomRevisionableTrait, CrudTrait;


    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'mediation_avail_times';
    protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = ['atMeridiem','scheduledDateTime'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */





    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function medmaster(){
        return $this->belongsTo(MediationMediator::class, 'at_m_id','id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function getatMeridiemAttribute()
    {
        return date("g:i A", strtotime($this->at_time));
    }

    public function getscheduledDateTimeAttribute()
    {
        return str_replace("00:00:00",$this->at_time,$this->at_begin);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
