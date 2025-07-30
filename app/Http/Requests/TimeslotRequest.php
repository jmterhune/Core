<?php

namespace App\Http\Requests;

use App\Models\Court;
use App\Models\CourtPermission;
use App\Models\CourtTimeslot;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TimeslotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $court = Court::find($this->court_id);

        if(backpack_user()->hasRole([ 'System Admin'])){
            return true;
        }

        $permission = CourtPermission::where('judge_id', $court->judge->id)->where('user_id',backpack_user()->id)->first();

        if($permission == null ){
            abort(403);
        }

        return (backpack_auth()->check() && $permission->editable);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $start = Carbon::create($this->t_start);
        $end = Carbon::create($this->t_end);

        //dd($this->t_end, $end->startOfDay()->hour(17)->minute(30)->toDateTimeString());

        return [
            't_start' => 'after:' .$start->startOfDay()->hour(6)->minute(59)->toDateTimeString() .'|before:' . $end->startOfDay()->hour(17)->minute(30)->toDateTimeString(),
            't_end' => 'after:' .$start->startOfDay()->hour(8)->minute(0)->toDateTimeString() .'|before:' . $end->startOfDay()->hour(17)->minute(30)->toDateTimeString(),

        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            't_start.before' => 'Invalid Start Time',
            't_start.after' => 'Invalid Start Time',
            't_end.after' => 'Invalid End Time',
            't_end.before' => 'Invalid End Time'
        ];
    }
}
