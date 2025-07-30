<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtMotions extends Model
{
    use HasFactory;

    protected $table = 'court_motions';
    protected $fillable = ['court_id','motion_id','allowed'];

    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableAttribute()
    {
        return $this->motion;
    }

    public function motions(){
        return $this->hasMany(Motion::class,'motion_id','id');
    }
}
