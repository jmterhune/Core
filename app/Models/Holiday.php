<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'holidays';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    protected $appends = ['title','allDay','display','overlap','selectable','blocked'];

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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    protected function title(): Attribute
    {
        return new Attribute(
            get: fn() => $this->name
        );
    }

    protected function getBlockedAttribute(){
        return true;
    }

    protected function getStartEditableAttribute(){
        return false;
    }
    protected function getOverlapAttribute()
    {
        return false;
    }

    protected function getSelectableAttribute()
    {
        return false;
    }
    protected function getDisplayAttribute()
    {
        return 'auto';
    }

    protected function getAllDayAttribute()
    {
        return true;
    }
}
