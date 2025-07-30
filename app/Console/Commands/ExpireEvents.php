<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventStatus;
use Carbon\Carbon;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Illuminate\Console\Command;

class ExpireEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change events status to past if they are schedule for the past.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $valid = new DNSCheckValidation();
        $something = new EmailLexer();

        $statuses = EventStatus::select('id')->whereIn('name', ['Scheduled','Rescheduled'])->get();

        $hearings = Event::whereIn('status_id', $statuses)->get();

        foreach ($hearings as $hearing){

            if($hearing->timeslot != null){
                if($hearing->timeslot->end < Carbon::now()->timezone('America/New_York')){
                    $past = EventStatus::select('id')->where('name', ['Past'])->first();
                    $hearing->status_id = $past->id;
                    $hearing->owner_id = 1;
                    $hearing->owner_type = 'App\Models\User';
                    $hearing->save();
                }
            } else{
                dump($hearing->id);
            }
        }

        return Command::SUCCESS;
    }
}
