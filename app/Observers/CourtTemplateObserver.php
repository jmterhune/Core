<?php

namespace App\Observers;

use App\Models\CourtTemplateOrder;
use App\Models\CourtTimeslot;
use App\Models\Holiday;
use App\Models\Timeslot;
use Carbon\Carbon;

class CourtTemplateObserver
{
    /**
     * Handle the CourtTemplateOrder "created" event.
     *
     * @param  \App\Models\CourtTemplateOrder  $courtTemplateOrder
     * @return void
     */
    public function created(CourtTemplateOrder $courtTemplateOrder)
    {
        //
    }

    /**
     * Handle the CourtTemplateOrder "updated" event.
     *
     * @param  \App\Models\CourtTemplateOrder  $courtTemplateOrder
     * @return void
     */
    public function updated(CourtTemplateOrder $courtTemplateOrder)
    {
        $holidays = Holiday::all();

        if(!$courtTemplateOrder->auto){
            if($courtTemplateOrder->template != null){

               $week = Carbon::createFromFormat('D, F d, yy', $courtTemplateOrder->date);
               $timeslots = $courtTemplateOrder->template->timeslots;

               for($x = 0; $x < 5; $x++ ){

                   $day = $week->toDateString();

                   foreach($timeslots->where('day', $x + 1) as $timeslot){

                       $start = Carbon::createFromFormat('Y-m-d G:i:s', $day . ' ' . Carbon::create($timeslot->start)->toTimeString());
                       $end = Carbon::createFromFormat('Y-m-d G:i:s',$day . ' ' . Carbon::create($timeslot->end)->toTimeString());

                       if($holidays->doesntContain('date', $day)){
                           $new_timeslot = Timeslot::create([
                               'start' => $start,
                               'end' => $end,
                               'description' => $timeslot->description,
                               'allDay' => $timeslot->allDay,
                               'duration' => $timeslot->duration,
                               'quantity' => $timeslot->quantity,
                               'blocked' => $timeslot->blocked,
                               'block_reason' => $timeslot->block_reason,
                               'public_block' => $timeslot->public_block,
                               'category_id' => $timeslot->category_id,
                               'template_id' => $courtTemplateOrder->template->id ?? null,
                           ]);

                           CourtTimeslot::create([
                               'court_id' => $courtTemplateOrder->court_id,
                               'timeslot_id' => $new_timeslot->id
                           ]);
                       }
                   }

                   $week->addDay();
               }
            }
        }
    }

    /**
     * Handle the CourtTemplateOrder "deleted" event.
     *
     * @param  \App\Models\CourtTemplateOrder  $courtTemplateOrder
     * @return void
     */
    public function deleted(CourtTemplateOrder $courtTemplateOrder)
    {
        //
    }

    /**
     * Handle the CourtTemplateOrder "restored" event.
     *
     * @param  \App\Models\CourtTemplateOrder  $courtTemplateOrder
     * @return void
     */
    public function restored(CourtTemplateOrder $courtTemplateOrder)
    {
        //
    }

    /**
     * Handle the CourtTemplateOrder "force deleted" event.
     *
     * @param  \App\Models\CourtTemplateOrder  $courtTemplateOrder
     * @return void
     */
    public function forceDeleted(CourtTemplateOrder $courtTemplateOrder)
    {
        //
    }
}
