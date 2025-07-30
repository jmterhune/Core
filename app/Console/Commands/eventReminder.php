<?php

namespace App\Console\Commands;

use App\Models\Attorney;
use App\Models\Court;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\EventCrudController as EventCrudController;
use App\Mail\EventReminderMail;
use App\Models\EventReminder as EventReminderModel;

class eventReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running Job to send reminder email before one day of the event';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        date_default_timezone_set("US/Eastern");
        $datetime = new \DateTime('tomorrow');
        $tomorrow = $datetime->format('Y-m-d');

        // $reminder = new EventCrudController;
        $reminder = self::sendEventReminder($tomorrow);
        // $reminder->sendEventReminder($tomorrow);
    }

    function array_key_exists_wildcard ( $array, $search, $return = '' ) {
        $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
        $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
        if ( $return == 'key-value' )
            return array_intersect_key( $array, array_flip( $result ) );
        return $result;
    }


    function sendEventReminder($date = null)
    {
        $datetime = new \DateTime('tomorrow');
        $date = ($date) ? $date : $datetime->format('Y-m-d');

        $fromCurrentTime = date("H").":00:00";
        //  echo $fromCurrentTime."<br>";exit;
        $toCurrentTime = date("H").":59:59";
        $eventReminders = EventReminderModel::with('event')->where('event_start','>=',$date." ".$fromCurrentTime)->where('event_start','<=',$date." ".$toCurrentTime)->get();
        // $events = Event::whereHas('timeslot', function($q) use($date,$fromCurrentTime,$toCurrentTime){
        //     $q->where('start','>=',$date." ".$fromCurrentTime)->where('start','<=',$date." ".$toCurrentTime);
        // })->where(['reminder'=>1])->get();//$events ) Events :: whereHas('timeslot',function($q) use($date,$fromCurrentTime,$toCurrentTime))

        // echo json_encode($eventReminders);exit;   echo json_encode($eventReminders)

        foreach($eventReminders as $reminder){
            $event = $reminder->event;
            $subject = 'Reminder for Case #'.$event->case_num;

            $customFields=json_decode($event->template,true);
            
            
            $toEmails = $reminder->email;

            \Mail::to($toEmails)->send(new EventReminderMail($event));
        }

        return "Event reminders sent successfully!";
    }

}
