<?php

namespace App\Models;

use FontLib\Table\Type\post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediationCaseParties extends Model
{
    use HasFactory;

    protected $table = 'mediation_case_parties';
    protected $fillable = ['mediation_case_id', 'party_id'];
    public $timestamps = false;
}
