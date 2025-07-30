<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class FixCaseNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:fixcase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Correct Case Numbers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $events = Event::all();

        foreach ($events as $event){

            if(preg_match('/^\d{4}[-]\d{4}/', $event->case_num)){
                $new_code = $event->timeslot->court->county->code;
                $new_case_num = $new_code . substr($event->case_num, 4);
                $event->case_num = $new_case_num;
                $event->save();
            }

            if($event->case_num[0] == '-'){
                $new_code = $event->timeslot->court->county->code;
                $new_case_num = $new_code . $event->case_num;
                $event->case_num = $new_case_num;
                $event->save();
            }

            $county_code_check = substr($event->case_num, 2, 1);

            if($county_code_check != '-'){

                $county_code = substr($event->case_num, 0, 2);

                if($county_code != '59' && $county_code != '05'){
                    $new_code = $event->timeslot->court->county->code;
                    $new_case_num = $new_code . '-' . $event->case_num;
                    $event->case_num = $new_case_num;
                    $event->save();
                }
            }


        }

        return Command::SUCCESS;
    }
}
