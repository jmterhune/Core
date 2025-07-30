<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ExcelUploaded;
use App\Models\Category;
use App\Models\Court;
use App\Models\CourtPermission;
use App\Models\CourtTemplateOrder;
use App\Models\CourtTimeslot;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Holiday;
use App\Models\CourtType;
use App\Models\Timeslot;
use App\Models\TimeslotEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Prologue\Alerts\Facades\Alert;


class CalendarController extends Controller
{
    public function show(Court $calendar){


        $court = $calendar;

        $holidays = Holiday::all();

        $format = Court::select('case_num_format')->where('id',$calendar->id)->first();
        $court_types = CourtType::select('old_id')->get();
	    $event_types = EventType::all();

        if(backpack_user()->hasRole([ 'System Admin'])){
            return view('admin.cal',
                [
                    'editable' => true,
                    'court' => $court,
                    'event_types' => $event_types,
                    'categories' => Category::all()->sortBy('description'),
                    'timeslots' => [], //TODO: Needs review: don't think we need this
                    'blocked_timeslots' => [], //TODO: Needs review: I think we can get this data another way
                    'case_format' => $format,
                    'court_types' => $court_types,
                    'court_templates' => $court->templates->where('display_on_schedule',1)
                ]);
        }

        $permission = CourtPermission::where('judge_id', $calendar->judge->id)->where('active',true)->where('user_id',backpack_user()->id)->first();

        if($permission == null ){
            abort(403);
        }

        return view('admin.cal',
            [
                'editable' => $permission->editable,
                'court' => $court,
                'event_types' => $event_types,
                'categories' => Category::all()->sortBy('description'),
                'timeslots' => $calendar->timeslots,
                'blocked_timeslots' => $court->timeslots->where('blocked',1)->merge($holidays),
                'case_format' => $format,
                'court_types' => $court_types,
                'court_templates' => $court->templates->where('display_on_schedule',1)
            ]);
    }

    public function extend(Court $calendar){

        $last_timeslot = $calendar->timeslots()->orderBy('start', 'desc')->select('start')->first();
        $last_hearing = $calendar->timeslots()->whereHas('events')->orderBy('start', 'desc')->select('start')->first();
        $last_template_timeslot = $calendar->timeslots()->whereNotNull('template_id')->orderBy('start', 'desc')->first();

        $templates = CourtTemplateOrder::where('court_id', $calendar->id)->where('auto', true)->orderby('order')->get();

        return view('admin.extend',
            [
                'court' => $calendar, 'last_timeslot' => $last_timeslot, 'last_hearing' => $last_hearing,
                'templates' => $templates, 'last_template_timeslot' => $last_template_timeslot
            ]);
    }

    public function upload(Court $calendar){

        return view('admin.upload', ['court' => $calendar ]);
    }

    public function upload_data(Court $calendar, Request $request){

        //$this->createTimeslot($request->start_date);
        Excel::import(new ExcelUploaded, $request->file('file'));

        return redirect()->route('calendar.show',['calendar' => $request->court]);
    }



    public function extend_calendar(Court $calendar, Request $request){

        $last_template_timeslot = $calendar->timeslots()->whereNotNull('template_id')->orderBy('start', 'desc')->first();

        $holidays = Holiday::all();

        $ordered_templates = CourtTemplateOrder::where('court_id', $calendar->id)->where('auto', true)->orderby('order')->get();

        $start_order = $request->start_template;

        // Start a week after the last template timeslot otherwise start from the current week
        if($last_template_timeslot !== null){
            if($request->start_date == $last_template_timeslot->date){
                $start_week = Carbon::create($last_template_timeslot->start)->addWeek()->startOfWeek();
            } else{
                $start_week =Carbon::create($request->start_date)->startOfWeek();
            }
        } else{
            $start_week = Carbon::now()->startOfWeek();
        }


        for($x = 0; $x < $request->weeks; $x++){


            // Iterate through template timeslots starting at requested template
            if($x == 0){
                $current_template = $ordered_templates->where('order', $start_order)->first()->template;
                $timeslots = $ordered_templates->where('order', $start_order++)->first()->template->timeslots;
            } else{
                if($ordered_templates->where('order', $start_order)->first() !== null){
                    $current_template = $ordered_templates->where('order', $start_order)->first()->template;
                    $timeslots = $ordered_templates->where('order', $start_order++)->first()->template->timeslots;
                } else{
                    //Reset start order and start with first template
                    $start_order = 1;
                    $current_template = $ordered_templates->where('order', $start_order)->first()->template;
                    $timeslots = $ordered_templates->where('order', $start_order++)->first()->template->timeslots;
                }
            }


            for($y = 0; $y < 5; $y++){

                $day = Carbon::create($start_week)->toDateString();

                foreach($timeslots->where('day', $y + 1) as $timeslot){

                    $start = Carbon::createFromFormat('Y-m-d G:i:s', $day . ' ' . Carbon::create($timeslot->start)->toTimeString());
                    $end = Carbon::createFromFormat('Y-m-d G:i:s',$day . ' ' . Carbon::create($timeslot->end)->toTimeString());

                    $new_timeslot = Timeslot::create([
                        'start' => $start,
                        'end' => $end,
                        'description' => $timeslot->description,
                        'allDay' => $timeslot->allDay,
                        'duration' => $timeslot->duration,
                        'quantity' => $timeslot->quantity,
                        'blocked' => $holidays->doesntContain('date', $day) ? $timeslot->blocked : true,
                        'block_reason' => empty($timeslot->block_reason) ? null : $timeslot->block_reason,
                        'public_block' => $timeslot->public_block,
                        'category_id' => $timeslot->category_id,
                        'template_id' => $current_template->id ?? null,
                    ]);

                    CourtTimeslot::create([
                        'court_id' => $calendar->id,
                        'timeslot_id' => $new_timeslot->id
                    ]);
                }


                $start_week->addDay();
            }

            $start_week->addWeek();
            $start_week = $start_week->startOfWeek();
        }


        Alert::add('success', '<strong>Extending Successful</strong><br>')->flash();

        return redirect()->route('calendar.show', $calendar);
    }

    public function truncate(Court $calendar){

        $last_timeslot = $calendar->timeslots()->where('start', '>', Carbon::now())->orderBy('start', 'desc')->select('start')->first();
        $last_hearing = $calendar->timeslots()->whereHas('events')->orderBy('start', 'desc')->select('start')->first();

        return view('admin.truncate',
            ['court' => $calendar, 'last_timeslot' => $last_timeslot, 'last_hearing' => $last_hearing]);
    }

    public function truncate_timeslots(Court $calendar, Request $request){

        $start_date = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay();


        switch($request->filter){
            case "all":
                $timeslots = $calendar->timeslots()->where('start', '>=' ,$start_date)->get();
                foreach($timeslots as $timeslot){
                    foreach($timeslot->events as $event){

                        TimeslotEvent::where('event_id', $event->id)->delete();

                        Event::find($event->id)->update([
                            'status_id' => 1
                        ]);
                    }

                    foreach($timeslot->motions as $motion){
                        $motion->delete();
                    }

                    $timeslot->delete();
                }
                break;
            case "hearings":
                $timeslots = $calendar->timeslots()->where('start', '>=', $start_date)
                    ->whereDoesntHave('events')->get();

                foreach($timeslots as $timeslot){

                    foreach($timeslot->motions as $motion){
                        $motion->delete();
                    }

                    $timeslot->delete();
                }

                break;

            case "templates":
                $timeslots = $calendar->timeslots()->where('start', '>=', $start_date)
                    ->whereNotnull('template_id')->where('blocked', false)->get();

                foreach($timeslots as $timeslot){

                    foreach($timeslot->motions as $motion){
                        $motion->delete();
                    }

                    $timeslot->delete();
                }

                break;

            case "both":
                $timeslots = $calendar->timeslots()->where('start', '>=', $start_date)
                    ->whereDoesntHave('events')->whereNotnull('template_id')->get();

                foreach($timeslots as $timeslot){

                    foreach($timeslot->motions as $motion){
                        $motion->delete();
                    }

                    $timeslot->delete();
                }

                break;
        }



        Alert::add('success', '<strong>Truncate Successful</strong><br>')->flash();

        return redirect()->route('calendar.show', $calendar);
    }

    public function extend_manual(Court $calendar){

        $templates = $calendar->template_order_manual;

        $holidays = Holiday::all();

        foreach($templates as $courtTemplateOrder){
            if($courtTemplateOrder->template != null){

                $week = Carbon::createFromFormat('D, F d, yy', $courtTemplateOrder->date);
                $timeslots = $courtTemplateOrder->template->timeslots;

                for($x = 0; $x < 5; $x++ ){

                    $day = $week->toDateString();

                    foreach($timeslots->where('day', $x + 1) as $timeslot){

                        $start = Carbon::createFromFormat('Y-m-d G:i:s', $day . ' ' . Carbon::create($timeslot->start)->toTimeString());
                        $end = Carbon::createFromFormat('Y-m-d G:i:s',$day . ' ' . Carbon::create($timeslot->end)->toTimeString());

                        if($holidays->doesntContain('date', $day)){

                            $current_timeslots = $calendar->timeslots()->whereDate('start', $start->toDateString())->get();

                            $match = $current_timeslots->where('start', $start->toDateTimeString())->where('template_id', $courtTemplateOrder->template->id);

                            if($match->isEmpty()){

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

                                if($new_timeslot->wasRecentlyCreated){
                                    CourtTimeslot::create([
                                        'court_id' => $courtTemplateOrder->court_id,
                                        'timeslot_id' => $new_timeslot->id
                                    ]);
                                }
                            }
                        }
                    }

                    $week->addDay();
                }
            }
        }

        Alert::add('success', '<strong>Extending Successful</strong><br>')->flash();

        return redirect()->route('calendar.show', $calendar);
    }
}
