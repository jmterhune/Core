<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class CourtPermission extends Model
{
    use CrudTrait, CustomRevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'court_permissions';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['user_id','judge_id','editable','active'];
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
        return Judge::find($this->judge_id)->name;
    }

    protected $revisionFormattedFields = [
        'active'     => 'boolean:No|Yes',
        'editable' => 'boolean:View Only|View and Edit'
    ];

    protected $revisionFormattedFieldNames = [
        'editable'      => 'permission',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function ja(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function judges(){
        return $this->belongsTo('App\Models\Judge','judge_id','id');
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
