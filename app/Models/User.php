<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Venturecraft\Revisionable\RevisionableTrait;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens, Notifiable, CrudTrait, HasRoles, CustomRevisionableTrait, AuthenticatesWithLdap;

    public $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $appends = [ 'old_id'];

    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableName()
    {
        return $this->name;
    }

    public function courts(): array
    {
        $judges = CourtPermission::where('user_id', backpack_user()->id)->where('active', true)->get();
        $courts = [];

        foreach($judges as $judge){
            $courts[] = Judge::find($judge->judge_id)->court ? Judge::find($judge->judge_id)->court->id : null;
        }

        if(backpack_user()->hasRole([ 'JA'])){
            return $courts;
        } else{
            return Court::select('id')->get()->toArray();
        }
    }

    public function courtsFilter(): array
    {
        if(backpack_user()->hasRole(['System Admin'])){
            $judges = Judge::whereHas('court')->get();
            $courts = [];

            foreach($judges as $judge){
                $court = $judge->court;
                $courts[$court->id] = $court->description ;
            }
        } else {
            $judges = CourtPermission::where('user_id', backpack_user()->id)->where('active',true)->get();
            $courts = [];

            foreach($judges as $judge){
                $court = Judge::find($judge->judge_id)->court;
                if($court != null){
                    $courts[$court->id] = $court->description ;
                }
            }
        }
        asort($courts);

        return $courts;
    }

    public function tickets()
    {
        return $this->morphMany('App\Tickets', 'usertickets');
    }

    public function getOldIdAttribute()
    {
        $letter = $this->name[0];
        $parse = explode(' ', $this->name);

        return strtoupper($letter . end($parse));
    }

    public function getticketuserroleAttribute()
    {
        return "JA";
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



}
