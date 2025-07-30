<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TemplateTimeslot extends Model
{

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'template_timeslots';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = ['update_url', 'edit_url','delete_url','title','color'];
    protected $casts = ['allDay' => 'boolean',];
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    protected function updateUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('court_template.update', $this->id)
        );
    }

    protected function editUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('court_template.edit', $this->id)
        );
    }

    protected function deleteUrl(): Attribute
    {
        return new Attribute(
            get: fn() => route('court_template.destroy', $this->id)
        );
    }

    protected function title(): Attribute
    {
        $start = Carbon::create($this->start)->timezone('America/New_York');
        $end = Carbon::create($this->end)->timezone('America/New_York');
        $diff = $start->diffInMinutes($end);
        $available = $this->quantity * $this->duration;
        $events = TimeslotEvent::where('timeslot_id', $this->id)->get();

        if($available > $diff){
            $host = $_SERVER['SERVER_NAME'];

            $title =  $this->description . '<br>' .(floor($diff/$this->duration) - $events->count())  . ' Available <Br> ' . $this->quantity - floor($diff/$this->duration) .' Overbooked';

        }else{
            if($this->description != null){
                $title = $this->description . '<br> ';
            } else if($this->block_reason != null){
                $title = $this->block_reason . '<br> ';
            } else{
                $title = '';
            }

            if($this->category != null){
                $title .= '(' . $this->category->description . ') <Br>';
            }

            $title .=  $this->quantity . ' Available';


	}
	if ($this->public_block) {
        $title = ($title ? $title . '<br>' : '') . 'Public Blocked';
    }

        return new Attribute(
            get: fn() => $title
        );
    }

    protected function getColorAttribute(){
        $color = null;

        $start = Carbon::create($this->start)->timezone('America/New_York');
        $end = Carbon::create($this->end)->timezone('America/New_York');
        $diff = $start->diffInMinutes($end);
        $available = $this->quantity * $this->duration;

        if($available > $diff && ($_SERVER['SERVER_NAME'] != 'jacs.flcourts18.net') ){
            if($this->blocked){
                $color = '#808080';
            } else {
                $color = '#dc3545';
            }
        }else{

            if($this->blocked){
                $color = '#808080';
            } else{
                $color = '#007bff';
            }

        }

        return $color;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function motions(){
        return $this->morphMany(TimeslotMotion::class, 'timeslotable');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id','id');
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
