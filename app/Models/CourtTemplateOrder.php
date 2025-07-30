<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use function Illuminate\Events\queueable;

class CourtTemplateOrder extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'court_template_order';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    //protected $fillable = ['court_id','date','auto','template_id','created_at','updated_at'];
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
        static::updated(function ($courtTemplateOrder) {

            $holidays = Holiday::all();

            if(!$courtTemplateOrder->auto){
                if($courtTemplateOrder->template != null){

                    $week = Carbon::createFromFormat('D, F d, yy', $courtTemplateOrder->date);
                    $timeslots = $courtTemplateOrder->template->timeslots;

                    for($x = 0; $x < 5; $x++ ){

                        $day = $week->toDateString();

                        foreach($timeslots->where('day', $x + 1) as $timeslot){

                            $start = Carbon::createFromFormat('Y-m-d G:i:s', $day . ' ' . Carbon::create($timeslot->start)->toTimeString());
                            $end = Carbon::createFromFormat('Y-m-d G:i:s',$day . ' ' . Carbon::create($timeslot->end)->toTimeString());

                            if($holidays->doesntContain('date', $day)){

                                $new_timeslot = Timeslot::create([
                                    'start' => $start,
                                    'end' => $end,
                                    'description' => $timeslot->description,
                                    'allDay' => $timeslot->allDay,
                                    'duration' => $timeslot->duration,
                                    'quantity' => $timeslot->quantity,
                                    'blocked' => $timeslot->blocked,
                                    'public_block' => $timeslot->public_block,
                                    'block_reason' => $timeslot->block_reason,
                                    'category_id' => $timeslot->category_id,
                                    'template_id' => $courtTemplateOrder->template->id ?? null,
                                ]);

                                CourtTimeslot::create([
                                    'court_id' => $courtTemplateOrder->court_id,
                                    'timeslot_id' => $new_timeslot->id
                                ]);
                            }
                        }

                        $week->addDay();
                    }
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function template(){
        return $this->belongsTo('App\Models\Template', 'template_id','id');
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

    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::create($value)->format('D, F d, Y'),
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
