<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDefinedFields extends Model
{
    use HasFactory;
    protected $table = "user_defined_fields";
    protected $fillable = ['court_id','field_name','field_type','alignment','default_value','required','yes_answer_required','display_on_docket','display_on_schedule','use_in_attorany_scheduling','old_id'];

public function court(){
        return $this->hasOne(Court::class,'court_id','id');
    }
}
