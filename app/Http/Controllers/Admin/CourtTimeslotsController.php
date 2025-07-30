<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtTimeslot;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\TemplateTimeslot;
use App\Models\Timeslot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PDF;

class CourtTimeslotsController extends Controller
{
    public function show(Court $court_timeslot, Request $request){
        // dd(55);
        $start_time = Carbon::create($request->start);

        $holiday = Holiday::all();
        return $court_timeslot->timeslots->where('start', '>=', $start_time->addDays(-1))->where('start', '<=', $request->end)->mergeRecursive($holiday);
    }

    public function available_timeslots(Court $court, Request $request){
        $holiday = Holiday::all();


        $query = $court->timeslots()->where('blocked', false)
            ->where('start','>=',Carbon::today('America/New_York')->startOfDay())
            ->where('start', '<=', $request->end);

        if(isset($request->duration)){
            $query->where('duration', '>=', $request->duration);
        }

        if(isset($request->category)){
            $query->where('category_id', $request->category);
        }

        if(isset($request->motion)){
            $motion = $request->motion;

            $query->where(function ($query) use ($motion){
                $query->whereDoesntHave('motions')->orWherehas('motions', function (Builder $query) use ($motion){
                    $query->where('motion_id',$motion);
                });
            });

        }

        return $query->withCount('events')->get()->filter(function($item){ return $item['quantity'] > $item['events_count'];})->mergeRecursive($holiday);
    }

    public function month(Court $court_timeslot, Request $request){

        $start_time = Carbon::create($request->start);
        $end_time = Carbon::create($request->end);
        $week_template = [];

       $period = CarbonPeriod::create($start_time, '1 day', $end_time);

       foreach ($period as $key => $date){
           //$count = $court_timeslot->timeslots()->whereDay('start', $date->day)->whereMonth('start', $date->month)->whereHas('events')->count();
            $blockedSlots = $court_timeslot->timeslots()->whereDay('start', $date->day)->whereMonth('start', $date->month)->whereYear('start', $date->year)
            ->where(function ($query) {
                $query->where('blocked',true)
                      ->orWhere('public_block',true);
            })
            ->orderBy('start','asc')
            ->get();

        $scheduledEvents = \DB::select("select events.id from events
        inner join timeslot_events  on `timeslot_events`.`event_id` = `events`.`id`
        inner join timeslots on  `timeslots`.`id` = `timeslot_events`.`timeslot_id`
        inner join court_timeslots on court_timeslots.timeslot_id =  `timeslot_events`.`timeslot_id`
        where day(`start`) = ".$date->day." and month(`start`) = ".$date->month." and year(`start`) = ".$date->year." and court_id = ".$court_timeslot->id." and status_id != 1 and `blocked` != 1 and timeslot_events.deleted_at is null and timeslots.deleted_at is null");

           $start_of_week = Carbon::create($date)->format("Y-m-d");
           $end_of_week = Carbon::create($date)->format("Y-m-d");
           if( count($blockedSlots) > 0){
           $blocked = "Blocked<br>";
           }

           if(count($scheduledEvents) > 0)
           {
            $scheduledEventstitle = count($scheduledEvents)." Events Scheduled<br>";
           }

            foreach($blockedSlots as $blockedSlot)
            {
                $blocked .= $blockedSlot->start_time. " - ". $blockedSlot->end_time. "<br>";
		        $blocked .= ($blockedSlot->block_reason != null) ? $blockedSlot->block_reason."<br>" : ($blockedSlot->description != null ? $blockedSlot->description."<br>" : null);
		        $start_of_week = Carbon::create($blockedSlot->start)->format("Y-m-d");
                $end_of_week = Carbon::create($blockedSlot->end)->format("Y-m-d");
            }
            $title = null;
//           if($court_timeslot->auto_extension){
//
//               $templates = $court_timeslot->template_order_auto;
//
//              if($templates->isNotEmpty()){
//                   try{
//                       $title = $templates[$key]->template->name;
//                   } catch(ErrorException){
//
//                       if(count($templates) > $key){
//                           dump('how');
//                           $title = $templates[$key - count($templates)]->template->name ?? null;
//                       } else{
//                           $title = null;
//                       }
//                   }
//               } else{
//                   $title = null;
//               }
//
//           } else{
//               $templates = $court_timeslot->template_order_manual;
//
//               if($templates->where('date', $date->addDay(1)->format('D, F d, Y'))->isNotEmpty()){
//
//                   $title = $templates->where('date', $date->format('D, F d, Y'))->whereNotNull('template_id')->first()->template->name ?? null;
//
//               } else{
//                   $title = null;
//               }
//
//           }





            $sortOrder = 0;
            if(count($scheduledEvents) > 0){
                $week_template[] = [
                    'order' => $sortOrder,
                    'color' => 'green',
                    'start' => $start_of_week,
                    'end' => $end_of_week,
                    'allDay' => true,
                    'title' => $scheduledEventstitle
                ];
                $sortOrder++;
                }
           if( count($blockedSlots) > 0){
            $title .= ($blocked != 'Blocked<br>') ? $blocked : null;
           $week_template[] = [
                'order' => $sortOrder,
               'start' => $start_of_week,
               'end' => $end_of_week,
               'allDay' => true,
               'title' => $title
           ];
           $sortOrder++;
       }

       }
        $hearings = collect($week_template);
        return $hearings;
    }
    public function printPDF($court_id,$startDate,$endDate){


        $court_timeslot = Court::find($court_id);
        // $start_time = Carbon::create($startDate);

        $start_date = Carbon::create($startDate);
        $end_month = Carbon::create($startDate);
        // $end_date = Carbon::create($endDate);
        $start_time = $start_date->startOfMonth();
        $end_time = $end_month->endOfMonth();

	$start_date = Carbon::create($startDate);
        $end_month = Carbon::create($startDate);
        // $end_date = Carbon::create($endDate);
        $start_time = $start_date->startOfMonth();
        $end_time = $end_month->endOfMonth();
        $hearing_counts = [];

       $period = CarbonPeriod::create($start_time, $end_time);

       foreach ($period as $date){

           $count = $court_timeslot->timeslots()->whereDay('start', $date->day)->whereMonth('start', $date->month)->get()->where('available', true)->where('blocked',false)->count();
           $courtRes = $court_timeslot->timeslots()->whereDay('start', $date->day)->whereMonth('start', $date->month)->count();


           if($court_timeslot->auto_extension){
               $templates = $court_timeslot->template_order_auto;

               if($templates->isNotEmpty()){
                   try{
                       $title = $templates[$date->weekOfMonth -1]->template->name;
                   } catch(ErrorException){
                       if(count($templates) > $date->weekOfMonth){
                           $title = $templates[($date->weekOfMonth - 1) - count($templates)]->template->name;
                       } else{
                           $title = null;
                       }
                   }

               } else{
                   $title = null;
               }

           } else{
               $templates = $court_timeslot->template_order_manual;

               if($templates->isNotEmpty()){
                   $title = $templates[0]->template->name ?? null;
               } else{
                   $title = null;
               }
           }


           if($date->isWeekday() && $courtRes != 0){
               $hearing_counts[] = [
                   'start' => $date->startOfDay(),
                   'end' => $date->endOfDay(),
                   'allDay' => true,
                   'title' => $count . ' Free Timeslots',
                   'tCount' =>  $count,
                   'timeslotDescription' =>  $title
               ];
           }
       }

        $hearings = collect($hearing_counts);

        $weekGp = $hearings->groupBy(function ($item) use ($start_time){
            $date = Carbon::parse($item['start']);
            $startDate = $item['start'];
            $weekNo = $date->diffInWeeks($start_time);

            //if($weekNo == 0){
              //  $weekStartDate = $startDate->startOfMonth()->format('m/d/Y');
            //}else{
                $weekStartDate = $startDate->startOfWeek()->format('m/d/Y');
           // }
            $weekEndDate = $startDate->endOfWeek()->format('m/d/Y');
            $weekDates = '('.$weekStartDate . ' - ' .$weekEndDate . ')';
            return ['Week '.($weekNo +1) => $weekDates];
        });
// dd($weekGp);
        $data= [
            'court_timeslots' => $weekGp,
            'courtName' =>$court_timeslot->description ?? '',
            'pdfTitle' => "Monthly Calender for ".date('F Y',strtotime($start_time))
        ];
        // $data['court_timeslots']= $weekGp->mergeRecursive($blocked);
        $pdf = PDF::loadView('admin.pdfcal', $data);
        return $pdf->download('pdfcal.pdf');

    }
}
