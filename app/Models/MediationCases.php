<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Http\Traits\CustomRevisionableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediationCases extends Model
{
    use CustomRevisionableTrait, CrudTrait, SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'mediation_cases';
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

    public function attorney(){
        return $this->belongsTo('App\Models\Attorney');
    }



    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function judge(){
        return $this->belongsTo(Judge::class, 'c_div','id',MediationCases::class);
    }

    public function PltfAttroney(){
        return $this->belongsTo(Attorney::class, 'c_Pltf_a_id','id',MediationCases::class);
    }

    public function DefAttroney(){
        return $this->belongsTo(Attorney::class, 'c_def_a_id','id',MediationCases::class);
    }

    public function events(){
        return $this->hasMany(MediationEvents::class, 'e_c_id','id',MediationCases::class);
    }

    public function payments(){
        return $this->hasMany(MediationCaseEventPayments::class, 'p_c_id','id',MediationCases::class);
    }

    public function parties(){
        return $this->hasMany(Party::class, 'mediation_case_id','id',MediationCases::class);
    }

    public function attorneyParties(){
        return $this->hasMany(Party::class, 'mediation_case_id','id',MediationCases::class)->whereIn('type',['plaintiff','petitioner']);
    }

    public function defendantParties(){
        return $this->hasMany(Party::class, 'mediation_case_id','id',MediationCases::class)->whereIn('type',['defendant','respondent']);
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
