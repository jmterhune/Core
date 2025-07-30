<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeslotEventRequest;
use App\Mail\HearingConfirmation;
use App\Mail\HearingRescheduling;
use App\Mail\NewAttorney;
use App\Models\CourtTimeslot;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\Timeslot;
use App\Models\TimeslotEvent;
use Carbon\Carbon;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Motion;
use App\Models\Attorney;
use App\Models\Court;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Foreach_;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class TimeslotEventsController extends Controller
{
    public function index(){

    }

    public function store(TimeslotEventRequest $request){

        $format = Court::select('case_num_format')->where('id',$request->court_id)->first();

        $vaidateCaseNumber = $this->validateCaseNumber($request->case_num,$format->case_num_format);

        if($vaidateCaseNumber != NULL)
        {
           return response()->json(['errors' => ["case_error" => [$vaidateCaseNumber]]], 422);
        }

        $this->validate($request, [
            'otherMotion' => 'max:255',
            'plaintiff' => 'max:255',
            'defendant' => 'max:255',
          //  'plaintiff_email' => 'nullable|email',
           // 'defendant_email' => 'nullable|email',
        ]);

        $this->validate($request, [
            'otherMotion' => 'max:255',
        ]);
        CRUD::setValidation(TimeslotEventRequest::class);

        $event = Event::create([
            'addon' => $request->addon == '1' ? true : null,
            'case_num' => $request->case_num,
            'motion_id' => $request->motion_id,
            'custom_motion' => $request->otherMotion ?? '',
            'type_id' => $request->type_id,
            'attorney_id' => $request->attorney_id,
            'opp_attorney_id' => $request->opp_attorney_id,
            'plaintiff' => $request->plaintiff,
            'defendant' => $request->defendant,
            'notes' => $request->notes,
            'owner_id' => Auth::user()->id,
            'owner_type' => 'App\Models\User',
            'status_id' => EventStatus::where('name','Scheduled')->first()->id,
            'template' => json_encode($request->templates_data),
            'plaintiff_email' => empty($request->plaintiff_email) ? null : implode( ';', $request->plaintiff_email),
            'defendant_email' => empty($request->defendant_email) ? null : implode(';', $request->defendant_email),
        ]);

        TimeslotEvent::create([
            'timeslot_id' => $request->timeslot_id,
            'event_id' => $event->id
        ]);

        if($event->timeslot->blocked || $event->timeslot->public_block){
            $timeslot = $event->timeslot;
            $timeslot->blocked = 0;
            $timeslot->public_block = 0;
            $timeslot->save();
        }

        // Check if Email confirmations are enabled
        if($event->timeslot->court->email_confirmations){

            $emailFind=self::array_key_exists_wildcard($request->templates_data, '*_|EMAIL', 'key-value');


            $toEmails = [];
            if(isset($event->attorney->email) && $event->attorney->email->isNotEmpty())
            {
                foreach ($event->attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }
            if(isset($event->attorney->opp_attorney->email) && $event->opp_attorney->email->isNotEmpty())
            {
                foreach ($event->opp_attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }

            if(!empty($request->plaintiff_email))
            {
                foreach($request->plaintiff_email as $email){
                    $toEmails[] = $email;
                }
            }
            if(!empty($request->defendant_email))
            {
                foreach($request->defendant_email as $email){
                    $toEmails[] = $email;
                }
            }

            foreach($event->timeslot->court->judge->ja as $ja){
                $toEmails[] = $ja->email;
            }

            if(!empty($emailFind) && count($emailFind) > 0 ){

                $toEmails=array_merge( array_filter($emailFind), $toEmails);
            }

            if(!empty(array_unique($toEmails))){

                foreach(array_unique($toEmails) as $email){
                    Mail::to($email)->send(new HearingConfirmation($event));
                }
            }
        }

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Hearing Rescheduling to different Timeslot
     *
     * @param Timeslot $timeslot
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Timeslot $timeslot_event, TimeslotEventRequest $request){

        $old = Timeslot::find($request->old_timeslot_id);
        $event = Event::find($request->event_id);

        TimeslotEvent::where('timeslot_id', $request->old_timeslot_id)->where('event_id', $request->event_id)
            ->update(['timeslot_id' => $timeslot_event->id]);

        $event->status_id = EventStatus::where('name','Rescheduled')->first()->id;
        $event->save();

        // Check if Email confirmations are enabled
        if($timeslot_event->court->email_confirmations){

            $emailFind=self::array_key_exists_wildcard($event->templates_data, '*_|EMAIL', 'key-value');
            $toEmails = [];
            if (isset($event->attorney->email) && $event->attorney->email->isNotEmpty()) {
                foreach ($event->attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }
            if (isset($event->opp_attorney->email) && $event->opp_attorney->email->isNotEmpty()) {
                foreach ($event->opp_attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }

            if(!empty($event->plaintiff_email))
            {
                foreach($request->plaintiff_email as $email){
                    $toEmails[] = $email;
                }
            }
            if(!empty($event->defendant_email))
            {
                foreach($request->defendant_email as $email){
                    $toEmails[] = $email;
                }
            }
            foreach($event->timeslot->court->judge->ja as $ja){
                $toEmails[] = $ja->email;
            }

            if(!empty($emailFind) && count($emailFind) > 0 ){
                $toEmails=array_merge( array_filter($emailFind), $toEmails);
            }

            foreach(array_unique($toEmails) as $email){
                Mail::to($email)->send(new HearingRescheduling($event, $old));
            }
        }

        return response()->json(['success' => 'success'], 200);

    }
    function array_key_exists_wildcard ( $array, $search, $return = '' ) {
        if($array != null && $array[array_key_first($array)] != null){
            $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
            $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
            if ( $return == 'key-value' )
                return array_intersect_key( $array, array_flip( $result ) );
            return $result;
        }
    }

    public function validateCaseNumber($caseNumber, $caseFormat)
    {
        $case_number_error = null;
        if($caseFormat != null)
        {
            $caseFormatArray = explode("-",$caseFormat);
            $caseNumberArray = explode("-",$caseNumber);
            if(count($caseFormatArray) == 1)
            {
                $case_number_error = (strlen($caseNumber) == 0) ?  "Please provide Full UCN!" : null;
            }
            else if(count($caseFormatArray) == 2)
            {
                if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                {
                    $case_number_error = "Please provide complete year!";
                }
                else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 ||  strlen($caseNumberArray[1]) > 8)
                {
                    $case_number_error = "Please provide valid case number!";
                }
            }
            else if(count($caseFormatArray) == 3)
            {
                if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                {
                    $case_number_error = "Please provide complete year!";
                }
                else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 )
                {
                    $case_number_error = "Please select Case Code!";
                }
                else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 ||  strlen($caseNumberArray[2]) > 7)
                {
                    $case_number_error = "Please provide valid case number!";
                }
            }
            else if(count($caseFormatArray) == 3)
            {
                if(strlen($caseFormatArray[1]) == 2 || $caseFormatArray[1]==0 ){
                    if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                    {
                        $case_number_error = "Please provide complete year!";
                    }
                    else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 )
                    {
                        $case_number_error = "Please select Case Code!";
                    }
                    else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 ||  strlen($caseNumberArray[2]) > 7)
                    {
                        $case_number_error = "Please Enter case number!";
                    }
                }
                else{
                    if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                    {
                        $case_number_error = "Please provide complete year!";
                    }
                    else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 || strlen($caseNumberArray[2]) > 7)
                    {
                        $case_number_error = "Please Enter case number!";
                    }
                    else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 ||  strlen($caseNumberArray[2]) > 4)
                    {
                        $case_number_error = "Please provide Party/Defendant Identifier!";
                    }
                }
            }
            else if(count($caseFormatArray) == 6 || count($caseFormatArray) == 5 || count($caseFormatArray) == 4){
                if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) < 1 ||  strlen($caseNumberArray[0]) > 2)
                {
                    $case_number_error = "Please provide valid county number!";
                }
                else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) != 4 ||  strlen($caseNumberArray[1]) > 4)
                {
                    $case_number_error = "Please provide complete year!";
                }
                else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 )
                {
                    $case_number_error = "Please select Case Code!";
                }
                else if(!isset($caseNumberArray[3]) || strlen($caseNumberArray[3]) < 2 || strlen($caseNumberArray[3]) > 6 )
                {
                    $case_number_error = "Please Enter case number!";
                }
                else if(!isset($caseNumberArray[4]) || strlen($caseNumberArray[4]) < 1 ||  strlen($caseNumberArray[4]) > 4)
                {
                    $case_number_error = "Please provide Party/Defendant Identifier!";
                }
                else if(!isset($caseNumberArray[5]) || strlen($caseNumberArray[5]) < 1 ||  strlen($caseNumberArray[5]) > 2)
                {
                    $case_number_error = "Please provide Branch Location";
                }
            }

        }
        else{
            $case_number_error = (strlen($caseNumber) == 0) ?  "Please Enter case number!" : null;
        }
        return $case_number_error;
    }
}
