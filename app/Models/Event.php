<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Event extends Model
{
    use CustomRevisionableTrait, CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'events';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = ['url'];
    //protected $keepRevisionOf = ['status_id'];


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function identifiableName()
    {
        return $this->case_num;
    }

    /**
     * Created a revision on the creation of a new Event
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function attorney(){
        return $this->belongsTo('App\Models\Attorney','attorney_id','id');
    }

    public function opp_attorney(){
        return $this->belongsTo('App\Models\Attorney','opp_attorney_id','id');
    }

    public function motion(){
        return $this->belongsTo(Motion::class,'motion_id','id');
    }

    public function status(){
        return $this->belongsTo(EventStatus::class,'status_id','id');
    }

    public function type(){
        return $this->belongsTo(EventType::class,'type_id','id');
    }

    public function timeslot(){
        return $this->HasOneThrough(Timeslot::class,TimeslotEvent::class,'event_id','id','id','timeslot_id')
            ->withoutGlobalScope('SoftDeletableHasManyThrough')->withTrashed();
    }

    public function category(){
        return $this->HasOneThrough(Timeslot::class,TimeslotEvent::class,'event_id','id','id','timeslot_id')
            ->withoutGlobalScope('SoftDeletableHasManyThrough')->withTrashed();
    }

     public function ownerable(){
         return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
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

    protected function url(): Attribute
    {
        return new Attribute(
            get: fn() => route('event.edit', [$this->id])
        );
    }

    protected function title(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ?? ''
        );
    }



    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
