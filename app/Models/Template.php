<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Illuminate\Events\queueable;

class Template extends Model
{
    use CrudTrait, CustomRevisionableTrait, SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'court_templates';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['name','court_id','old_id'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(queueable(function ($template) {
            $court = Court::find($template->court_id);

            if($court->auto_extension == 0){
                for($x = 0; $x < $court->calendar_weeks; $x++){
                    CourtTemplateOrder::firstOrCreate([
                        'court_id' => $court->id,
                        'date' => Carbon::now()->addWeeks($x)->startOfWeek(),
                        'auto' => 0
                    ]);
                }
            }
        }));
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function court(){
        return $this->belongsTo('App\Models\Court','court_id','id');
    }

    public function getJudge(){

        if(Judge::where('court_id', Template::find($this->id)->court_id)->first()){
            return Judge::where('court_id', Template::find($this->id)->court_id)->first()->name;
        } else{
            return null;
        }

    }

    public function motions(){
        return $this->morphMany(TimeslotMotion::class, 'timeslotable');
    }

    public function timeslots(){
        return $this->hasMany('App\Models\TemplateTimeslot', 'court_template_id', 'id');
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


    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


}
