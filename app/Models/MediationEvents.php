<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Http\Traits\CustomRevisionableTrait;
use Illuminate\Database\Eloquent\Model;

class MediationEvents extends Model
{
    use CustomRevisionableTrait, CrudTrait;


    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'mediation_events';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = ['ESchDatetimeAmpm'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

   
    public function getESchDatetimeAmpmAttribute()
    {
        return date("m-d-Y g:i A", strtotime($this->e_sch_datetime));
    }
    

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function case(){
        return $this->belongsTo(MediationCases::class, 'e_c_id','id');
    }

    public function medmaster()
    {
        return $this->belongsTo(MediationMediator::class, 'e_m_id','id');
    }

    public function outcome()
    {
        return $this->belongsTo(MediationOutcome::class, 'e_outcome_id','id');
    }

    public function payments(){
        return $this->hasMany(MediationCaseEventPayments::class, 'p_e_id','id',MediationEvents::class);
    }

    public function getEditEventURL(){
        $event = MediationEvents::with(['case'])->find($this->id);
        $casetype = "mediation";
        if($event->case->form_type == "f-form")
        {
            $casetype = "mediationfamily";
        }

         return '<a href="'. url($casetype.'/'.$event->e_c_id.'/edit?event_id='. $event->id) .'" target="_blank" lass="btn btn-sm btn-link" data-button-type="delete"><i class= "la la-calendar"></i>Edit </a>';
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
