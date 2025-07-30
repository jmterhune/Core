<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\Attorney;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Models\EventReminder;
use App\Models\Court;
use Illuminate\Http\Request;
use Twitter;
use Atymic\Twitter\Contract\Http\Client;


/**
 * Class EventCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EventReminderController extends CrudController
{

    function array_key_exists_wildcard ( $array, $search, $return = '' ) {
        if($array != null ) {
            $search = str_replace('\*', '.*?', preg_quote($search, '/'));
            $result = preg_grep('/^' . $search . '$/i', array_keys($array));
            if ($return == 'key-value')
                return array_intersect_key($array, array_flip($result));
            return $result;
        }
    }

    public function eventReminderEnable($eventId,$email){
        //EventReminder


        $event=Event::find($eventId);

        $customFields=json_decode($event->template,true);
            $toEmails = [];
            $emailFind=self::array_key_exists_wildcard($customFields, '*_|EMAIL', 'key-value');
            if(isset($event->attorney->email))
            {
                $toEmails[] = $event->attorney->email;
            }
            if(isset($event->opp_attorney->email))
            {
                $toEmails[] = $event->opp_attorney->email;
            }

            if(isset($event->plaintiff_email))
            {
                foreach(explode(';', $event->plaintiff_email) as $p_email){
                    $toEmails[] = $p_email;
                }

            }
            if(isset($event->defendant_email))
            {
                foreach(explode(';', $event->defendant_email) as $d_email){
                    $toEmails[] = $d_email;
                }

            }

            if(!empty($emailFind) && count($emailFind) > 0 ){
                $toEmails=array_merge( $emailFind, $toEmails);
            }

            array_unique($toEmails);

            if(in_array($email,$toEmails) && !isset(EventReminder::where('email',$email)->where('id',$eventId)->first()->id))
            {
                // Changed firstorCreate to prevent from duplication of emails
                EventReminder::firstOrCreate([
                    'email' => $email,
                    'event_id' => $eventId,
                    'event_start' => $event->timeslot->start
                ]);

                return "<h4> Reminder sucessfully Set</h4>";
            }
            else{
                // Changed to templated unauthorized TODO: Update 401 error page to remove "homepage"
                return abort('401');
            }

    }

    function downloadCourtEventCalendar($courtId,$fromDate,$toDate)
    {
        // $request = json_decode($request->getContent());
        $fromDate = date("Y-m-d",strtotime($fromDate));
        $toDate = date("Y-m-d",strtotime($toDate));
        $court = Court::where('id',$courtId)->first();
        $events=Event::with(['motion','type','attorney','opp_attorney'])->whereHas('timeslot.court', function($query) use($courtId){
            $query->where('court_timeslots.court_id',$courtId);
        })
        ->whereHas('timeslot', function($query) use($fromDate,$toDate){
            $query->whereDate('start','>=',$fromDate);
	    $query->whereDate('end','<',$toDate);
	    $query->where('timeslots.deleted_at',NULL);
        })
        ->get();
        $iCalEvents = [];
        define('DATE_ICAL', 'Ymd\THis');
    $output =
"BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:-spatie/icalendar-generator
X-WR-RELCALID:". $court->description."
X-WR-CALNAME:". $court->description."
CALSCALE:GREGORIAN\n";

        foreach($events as $event)
        {
            $description = "Case Number: ".$event->case_num. "\\n";
            $description .= "Motion: ". $event->motion->description . "\\n";
            $description .= "Hearing Type: ".  $event->type->name . "\\n";
            if($event->attorney != null)
            {
            $description .= "Attorney: ".$event->attorney->name . "\\n";
            }else{
                $description .= "Attorney:  \\n";
            }
            if($event->opp_attorney != null)
            {
            $description .= "Opposing Attorney: ". $event->opp_attorney->name . "\\n";
            }else{
                $description .= "Opposing Attorney:  \\n";
            }
            $description .= "Plaintiff: ".$event->plaintiff. "\\n";
            $description .= "Defendant: ".$event->defendant. "\\n";
            $description .= "Plaintiff Email: ".$event->plaintiff_email. "\\n";
            $description .= "Defendant Email: ".$event->defendant_email. "\\n";
            if(!empty($event->template))
            {
		    $customerFields = json_decode($event->template,true);
		    if($customerFields != NULL && $customerFields != "")
                {
                foreach ($customerFields as $key => $defined_data) {
                    $description .= explode("_|" , $key)[0].": ".$defined_data. "\\n";
                }}
            }
            $description .= "Notes: ".$event->notes. "\\n";

$output.=
"BEGIN:VEVENT
SUMMARY:" . $this->escapeString('Hearing of  '.$event->case_num) . "
DESCRIPTION:" .  $this->escapeString($description) . "
UID:$event->id
DTSTART:" . date(DATE_ICAL, strtotime($event->timeslot->start)) . "
DTEND:" . date(DATE_ICAL, strtotime($event->timeslot->end)) . "
DTSTAMP:" . date(DATE_ICAL, time()) . "
END:VEVENT\n";

    }

    $output .= "END:VCALENDAR";

    $md5 = md5($output);

    // file_put_contents("/var/www/icmsdata/tmp/$md5.ics", $output);
    // echo "$md5.ics";





        return response(  $output , 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'. $md5.'.ics"',
         ]);
    }
   public function escapeString($input) {
        $input = preg_replace('/([\,;])/','\\\$1', $input);
        $input = str_replace("\n", "\\n", $input);
        $input = str_replace("\r", "\\r", $input);
        return $input;
      }

    public function postTweet()
    {
        $querier = Twitter::forApiV2()->getQuerier();
        $result = $querier->post(
            'tweets',
            [
                Client::KEY_REQUEST_FORMAT => Client::REQUEST_FORMAT_JSON,
                'text' => 'Hello Jacs welcome!'
            ]
        );

        return $result;
    }

public function attorneyEnable($bar_num){

    $attorney=Attorney::where('bar_num',$bar_num)->update([
        'enabled' => 1,
    ]);

    return "<h4> Attorney Enabled sucessfully </h4>";

}


}
