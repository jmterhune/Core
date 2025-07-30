<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtEventTypes extends Model
{
    use HasFactory;

    protected $table = 'court_event_types';
    protected $fillable = ['court_id', 'event_type_id'];

    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableAttribute()
    {
        return $this->event_type;
    }

    public function event_type(){
        return $this->hasMany(EventType::class,'event_type_id','id');
    }
}
