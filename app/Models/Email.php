<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Email extends Model
{
    use HasFactory, CrudTrait;
    public $fillable = ['email','emailable_id','emailable_type'];

    public function attorney(){
        return $this->morphTo(__FUNCTION__, 'emailable_type','emailable_id');
    }

    public function identifiableAttribute()
    {
        return $this->emailable_id;
    }
}
