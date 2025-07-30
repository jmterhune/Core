<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Notifications\JacsResetPassword as ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Lang;
use Laravel\Sanctum\HasApiTokens;
use Venturecraft\Revisionable\RevisionableTrait;

class Attorney extends Authenticatable
{
    use HasApiTokens,CrudTrait,HasFactory,Notifiable, CustomRevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'attorneys';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['name','email','bar_num','phone','password','enabled','notes','password_changed_at','def_attorney_id','opp_attorney_id'];
    protected $hidden = ['password','remember_token'];
    // protected $dates = [];


    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    public function identifiableName()
    {
        return $this->name;
    }

    protected $revisionFormattedFields = [
        'enabled'     => 'boolean:No|Yes',
    ];

    /**
     * Created a revision on the creation of a new Attorney
     *
     * @var bool
     */
    protected $revisionCreationsEnabled = true;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        //$user = User::first();

        $this->notify(new ResetPasswordNotification($token));
    }

    public function getEmailForPasswordReset()
    {
        return $this->bar_num;
    }



    public function tickets()
    {
        return $this->morphMany('App\Tickets', 'usertickets');
    }

    public function getticketuserroleAttribute()
    {
        return "Attroney";
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
//    protected function buildMailMessage($url)
//    {
//        return (new MailMessage)
//            ->subject(Lang::get('Reset Password Notification'))
//            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
//            ->action(Lang::get('Reset Password'), $url)
//            ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
//            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
//    }
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function email()
    {
        return $this->morphMany(Email::class, 'emailable');
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
