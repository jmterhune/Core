<?php

namespace App\Console\Commands;

use App\Models\Court;
use App\Models\CourtTemplateOrder;
use App\Models\CourtTimeslot;
use App\Models\Holiday;
use App\Models\Template;
use App\Models\Timeslot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class AutoExtendCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jacs:auto';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Extend Calendars set up for automatic extensions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$courts = Court::where('auto_extension',true)->get();

        $calendars = Court::where('auto_extension', 1)->get();

        foreach ($calendars as $calendar){

            $holidays = Holiday::all()                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             ;

            $today = Carbon::now()->startOfDay();
            $end = $today->copy()->addWeeks($calendar->calendar_weeks);

            $last_timeslot = $calendar->timeslots()->select('start','template_id','timeslot_id')->whereNotNull('template_id')->get()->last();

            dump($calendar->description);

            // Exit if unable to determine template starting point
            if($last_timeslot != null){
                $start_template = Template::find($last_timeslot->template_id);
            } else{
                continue;
            }

            $period = CarbonPeriod::create(Carbon::create($last_timeslot->start), $end);

            // Filter out weekends
            $notWeekendFilter = function ($date) {
                return !$date->isWeekend();
            };
            $period->filter($notWeekendFilter);

            $order = $calendar->template_order_auto->where('template_id', $start_template->id)->first();

            // Break out of loop if court doesn't haven any order for templates
            if($order == null){
                continue;
            }
            foreach ($period as $key => $date) {

                if($key == 0 ){
                    $template_mask = $calendar->template_order_auto->where('order', $order->order)->first()->template;
                    $template_order = $order->order;
                } else{

                    if($calendar->template_order_auto->where('order', $template_order)->first() != null){
                        $template_mask = $calendar->template_order_auto->where('order', $template_order)->first()->template;
                    } else{
                        $template_order = 1;
                        $template_mask = $calendar->template_order_auto->where('order', $template_order)->first()->template;
                    }
                }

                $current_timeslots = $calendar->timeslots()->whereDate('start', $date->toDateString())->get();

                $template_timeslots = $template_mask->timeslots()->where('day', $date->dayOfWeek)->get();

                foreach ($template_timeslots as $template_timeslot){

                    $start = Carbon::createFromFormat('Y-m-d G:i:s', $date->format('Y-m-d') . ' ' . Carbon::create($template_timeslot->start)->toTimeString());
                    $end = Carbon::createFromFormat('Y-m-d G:i:s',$date->format('Y-m-d') . ' ' . Carbon::create($template_timeslot->end)->toTimeString());

                    if($holidays->doesntContain('date', $date->toDateString())){

                        //$current_timeslots = $calendar->timeslots()->whereDate('start', $start->toDateString())->withTrashed()->get();

                        $match = $current_timeslots
                            ->where('start', '<=', $start->toDateTimeString())
                            ->where('end', '>=', $end->toDateTimeString());
//                            ->where('quantity', $template_timeslot->quantity)
//                            ->where('duration', $template_timeslot->duration)
//                            ->where('blocked', $template_timeslot->blocked);

                        if($match->isEmpty() && $current_timeslots->where('created_at', '<', Carbon::now()->toDateString())->isEmpty()){
                            dump($start->toDateTimeString(), $end->toDateTimeString(),$match->isEmpty() && $current_timeslots->where('created_at', '<', Carbon::now()->toDateString())->isEmpty());
                        }

                        if($match->isEmpty() && $current_timeslots->where('created_at', '<', Carbon::now()->toDateString())->isEmpty()){
                            $new_timeslot = Timeslot::create([
                                'start' => $start,
                                'end' => $end,
                                'allDay' => $template_timeslot->allDay,
                                'duration' => $template_timeslot->duration,
                                'quantity' => $template_timeslot->quantity,
                                'blocked' => $template_timeslot->blocked,
                                'public_block' => $template_timeslot->public_block,
                                'block_reason' => empty($template_timeslot->block_reason) ? null : $template_timeslot->block_reason,
                                'category_id' => $template_timeslot->category_id,
                                'template_id' => $template_timeslot->court_template_id,
                                'description' => $template_timeslot->description
                            ]);

                            CourtTimeslot::create([
                                'court_id' => $calendar->id,
                                'timeslot_id' => $new_timeslot->id
                            ]);

                            $new_timeslot->save();
                        } else {
                            $dup = $match->first();

                            if($dup != null){
                                $dup->template_id = $template_timeslot->court_template_id;

                                $dup->save();
                            }
                        }
                    }
                }


                if($date->dayOfWeek == 5){
                    $template_order++;
                }

            }
        }

        return Command::SUCCESS;
    }
}
