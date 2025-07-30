<?php

namespace App\Imports;

use App\Models\Attorney;
use App\Models\CourtTimeslot;
use App\Models\Event;
use App\Models\Timeslot;
use App\Models\TimeslotEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Ramsey\Uuid\Type\Time;

class ExcelUploaded implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $timeslot = $this->createTimeslot(request()->start_date);

        // Removing Header Rows
        unset($rows[0]);
        unset($rows[1]);

        foreach ($rows as $key => $row)
        {

            if($row[1] != ""){
                $additional_plaintiff_attorney = [];
                $additional_defendant_attorney = [];

                $case_num = $this->caseNumFormat($row[1]);

                $plaintiff = $row[3];
                $defendant = $row[4];

                $plaintiff_attorney = null;
                $defendant_attorney = null;

                foreach ($this->findAttorney($row[5]) as $p_attorney){
                    if(gettype($p_attorney) == 'string'){
                        $additional_plaintiff_attorney[] = $p_attorney;
                    } else if($plaintiff_attorney == null) {
                        $plaintiff_attorney = $p_attorney->id;
                    } else{
                        $additional_plaintiff_attorney[] = $p_attorney->name;
                    }
                }

                foreach ($this->findAttorney($row[6]) as $o_attorney){
                    if(gettype($o_attorney) == 'string'){
                        $additional_defendant_attorney[] = $o_attorney;
                    } else if($defendant_attorney == null) {
                        $defendant_attorney = $o_attorney->id;
                    } else{
                        $additional_defendant_attorney[] = $o_attorney->name;
                    }
                }
                //dump($plaintiff_attorney, $additional_plaintiff_attorney, $defendant_attorney, $additional_defendant_attorney);

                $notes = $row[0] . ': ' . $row[9]; // Could be Updated per upload
                $status_id = 3;
                $type_id = 2;
                $motion_id = 92;
                $owner_id = 1;
                $owner_type = 'App\Models\User';

                $event = Event::updateOrCreate([
                    'case_num' => $case_num,
                ],
                [
                    'plaintiff' => $plaintiff,
                    'defendant' => $defendant,
                    'attorney_id' => $plaintiff_attorney,
                    'opp_attorney_id' => $defendant_attorney,
                    'notes' => $notes,
                    'status_id' => $status_id,
                    'type_id' => $type_id,
                    'motion_id' => $motion_id,
                    'owner_id' => $owner_id,
                    'owner_type' => $owner_type,
                ]);

                if($event->wasRecentlyCreated){
                    TimeslotEvent::create([
                        'timeslot_id' => $timeslot->id,
                        'event_id' => $event->id
                    ]);
                }
            }
        }
    }

    public function findAttorney($data){

        $attorneys = [];

        foreach(explode("\n", $data) as $attorney){
            if($attorney != "Pro Se"){
                $name = explode(' ', $attorney);

                if(count($name) == 2){
                    $found = Attorney::where('name', 'like',  '%' . $name[1] . ',' . $name[0] . '%')->first();
                    if($found == null){
                        $found = Attorney::where('name', 'like',  '%' . $name[1] . ', ' . $name[0] . '%')->first();

                        if($found == null){
                            $found = Attorney::where('name', 'like',  '%' . $name[1] . ' ' . $name[0] . '%')->first();
                        }
                    }
                }

                if(count($name) == 3 || count($name) == 4){
                    $found = Attorney::where('name', 'like',  '%' . $name[2] . ',' . $name[0] . ' ' . $name[1] .'%')->first();
                    if($found == null){
                        $found = Attorney::where('name', 'like',  '%' . $name[2] . ', ' . $name[0] . ' ' . $name[1] .'%')->first();

                        if($found == null){
                            $found = Attorney::where('name', 'like',  '%' . $name[2] . ' ' . $name[0] . ' ' . $name[1] .'%')->first();
                        }
                    }
                }

                !empty($found) ? $attorneys[] = $found : $attorneys[] = $attorney;

            } else{
                $attorneys[] = Attorney::where('name', 'like',  '%' . $attorney . '%')->first();
            }

        }

        return $attorneys;
    }

    public function createTimeslot($date){

        $timeslot = Timeslot::firstOrCreate([
            'start' => Carbon::create($date)->hour(9),
            'end' => Carbon::create($date)->hour(15)
        ],
            [
                'description' => 'PTC',
                'quantity' => 12,
                'duration' => 30,
                'allDay' => false,
                'blocked' => false,
                'public_block' => false,
            ]);

        CourtTimeslot::firstOrCreate([
            'court_id' => request()->court,
            'timeslot_id' => $timeslot->id
        ]);

         return $timeslot;
    }

    public function caseNumFormat($case_num){

        $format = '05-' . substr($case_num,0,4) . '-' . substr($case_num,4,2) . '-' . substr($case_num,6) . '-AXXX-XX';

        return $format;
    }

}
