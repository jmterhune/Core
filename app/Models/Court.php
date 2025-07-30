<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Court extends Model
{
    use CrudTrait, CustomRevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'courts';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableName()
    {
        return $this->description;
    }



    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function attorney(){
        return $this->belongsTo('App\Models\Attorney','def_attorney_id','id');
    }

    public function opp_attorney(){
        return $this->belongsTo('App\Models\Attorney','opp_attorney_id','id');
    }

    public function county(){
        return $this->belongsTo('App\Models\County','county_id','id');
    }

    public function motions(){
        return $this->belongsToMany(
            Motion::class, 'court_motions', 'court_id','motion_id'
        )->orderBy('description')->where('allowed', true);
    }

    public function restricted_motions(){
        return $this->belongsToMany(
            Motion::class, 'court_motions','court_id', 'motion_id'
        )->orderBy('description')->where('allowed', false);
    }

    public function event_types(){
        return $this->belongsToMany(
            EventType::class, 'court_event_types', 'court_id','event_type_id'
        );
    }

    public function backpack_events(){
        return $this->belongsTo('App\Models\Event','event_id','id');
    }

    public function judge(){
        return $this->hasOne(Judge::class);
    }



    public function scheduling_config(){
        return $this->hasOne('App\Models\CourtSchedulingConfig','court_id','id');
    }

    public function timeslots(){
        return $this->hasManyThrough(
            'App\Models\Timeslot',
            'App\Models\CourtTimeslot',
            'court_id',
            'id',
            'id',
            'timeslot_id');
    }

    public function template_order_auto(){
        return $this->hasMany(\App\Models\CourtTemplateOrder::class)->where('auto', true);
    }
    public function template_order_manual(){
        return $this->hasMany(\App\Models\CourtTemplateOrder::class)->where('auto', false)
            ->orderBy('date', 'asc')->where('date', '>=', Carbon::now()->startOfDay())->limit($this->calendar_weeks);
    }
    public function templates(){
        return $this->hasMany('App\Models\UserDefinedFields','court_id','id');
    }

    public function ja(){
        return $this->hasManyThrough(User::class, CourtPermission::class);
    }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

//        public function getDescriptionAttribute($value){
//
//
//            return $value . ' - ' . $this->judge->name;
//        }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
