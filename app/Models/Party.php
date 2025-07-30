<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Party extends Model
{
    use HasFactory;
    use CrudTrait;

    protected $fillable = ['name', 'attorney_id', 'address', 'email', 'telephone','type','mediation_case_id'];
    public $timestamps = false;


    public function attorney(){
        return $this->belongsTo(Attorney::class, 'attorney_id','id',Party::class);
    }

    public function PltfAttroney(){
        return $this->belongsTo(Attorney::class, 'attorney_id','id',Party::class);
    }

    public function DefAttroney(){
        return $this->belongsTo(Attorney::class, 'attorney_id','id',Party::class);
    }
}
