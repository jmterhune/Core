<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Notifications\JacsResetPassword as ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Lang;
use Laravel\Sanctum\HasApiTokens;
use Venturecraft\Revisionable\RevisionableTrait;


use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;

class Tickets extends Authenticatable
{
    use HasApiTokens,CrudTrait,HasFactory,Notifiable, CustomRevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'tickets';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = ['issue','email','bar_num','phone','password','enabled','notes','password_changed_at'];
    // protected $fillable = ['issue'];
    // protected $hidden = ['password','remember_token'];
    // protected $dates = [];
    protected $casts =[
        // 'emails' => 'array',
    ];


    /**
     * The name returned to the Revision
     *
     * @return mixed
     */
    // public function identifiableName()
    // {
    //     return $this->name;
    // }

    // protected $revisionFormattedFields = [
    //     'enabled'     => 'boolean:No|Yes',
    // ];

    /**
     * Created a revision on the creation of a new Attorney
     *
     * @var bool
     */
    // protected $revisionCreationsEnabled = true;

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     //$user = User::first();
    //     $this->notify(new ResetPasswordNotification($token));
    // }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // protected function buildMailMessage($url)
    // {
    //     return (new MailMessage)
    //         ->subject(Lang::get('Reset Password Notification'))
    //         ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
    //         ->action(Lang::get('Reset Password'), $url)
    //         ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
    //         ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    // }
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function status(){ 
        return $this->belongsTo(TicketStatus::class,'status_id','id');
    }
    public function priority(){
        return $this->belongsTo(TicketsPriority::class,'priority_id','id');
    }
    public function user(){
         return $this->belongsTo(User::class,'created_by','id');
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function owner(){
        return $this->morphTo(__FUNCTION__, 'created_user_type','created_by');
    }
    
    public function usertickets()
    {
        return $this->morphTo(__FUNCTION__, 'created_user_type','created_by');
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
    public static function boot()
    {
        parent::boot();
        static::deleting(function($obj) {
            Storage::delete(Str::replaceFirst('storage/','public/', $obj->file));
        });
    }


    public function setFileAttribute($value)
    {

        $attribute_name = "file";
        $disk = "ticket_uploads";
        $destination_path = "tickets"; //relative to $disk
        $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
    }
    public function getFileAttribute($value)
    {

       return "storage/".$value;
    }





}
