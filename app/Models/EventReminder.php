<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class EventReminder extends Model
{
    use CustomRevisionableTrait, CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'event_reminders';
    protected $guarded = ['id'];

    public function event(){
        return $this->belongsTo('App\Models\Event','event_id','id');
    }
   
}
