<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeslotRequest;
use App\Models\Court;
use App\Models\CourtPermission;
use App\Models\CourtTimeslot;
use App\Models\Holiday;
use App\Models\Template;
use App\Models\TemplateTimeslot;
use App\Models\Timeslot;
use App\Models\TimeslotMotion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    public function show(Template $court_template){
        return TemplateTimeslot::where('court_template_id', $court_template->id)->get();
    }

    public function update(TimeslotRequest $request, TemplateTimeslot $court_template){

        if($request->t_start == null){
            $start = Carbon::create($request->start)->timezone('America/New_York');
            $end = Carbon::create($request->end)->timezone('America/New_York');
        } else{
            $start = Carbon::create($request->t_start);
            $end = $request->t_end;
            $court_template->description = $request->description;
            $court_template->category_id = $request->category_id;
            $court_template->quantity = $request->quantity;
            $court_template->duration = $request->duration;
            $court_template->blocked = $request->blocked == 'on';
            $court_template->public_block = $request->public_block == 'on';
            $court_template->block_reason = $request->block_reason;
            //$template_timeslot->motions()->sync($request->timeslot_motions);

            if(empty($request->timeslot_motions)){
                TimeslotMotion::where('timeslotable_id', $court_template->id)->delete();
            } else{
                $old_restrcited_motions = TimeslotMotion::where('timeslotable_id', $court_template->id)->get();

                foreach($old_restrcited_motions as $motion){
                    if(!in_array($motion->id, $request->timeslot_motions)){
                        $motion->delete();
                    }
                }

                foreach($request->timeslot_motions as $motion){
                    TimeslotMotion::firstOrCreate([
                        'timeslotable_id' => $court_template->id,
                        'timeslotable_type' => 'App\Models\TemplateTimeslot',
                        'motion_id' => $motion
                    ]);
                }
            }
        }


        $court_template->start = $start;
        $court_template->end = $end;
        $court_template->day = $start->dayOfWeek;

        $court_template->save();

        return response()->json(['success' => 'success'], 200);
    }

    public function edit(TemplateTimeslot $court_template){
        return TemplateTimeslot::with('motions')->find($court_template->id);
    }

    public function destroy(TemplateTimeslot $court_template){


//        $court_timeslot = CourtTimeslot::where('timeslot_id',$timeslot->id)->first();
//
//        $court = Court::find($court_timeslot->court_id);
//
//        $permission = CourtPermission::where('judge_id', $court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();

        foreach($court_template->motions as $motion){
            $motion->delete();
        }

//        if(backpack_user()->hasRole(['System Admin'])){
//            $court_timeslot = CourtTimeslot::where('timeslot_id', $timeslot->id)->first();
//            $court_timeslot->delete();
//            $timeslot->delete();
//
//            return response()->json(['success' => 'success'], 200);
//        }
//
//        if(!$permission->editable){
//            abort(403);
//        }

        $court_template->delete();

        return response()->json(['success' => 'success'], 200);
    }

    public function store(TimeslotRequest $request){
        dd($request->all()); 

        $timeslots = $request->quantity;
        $start = Carbon::create($request->t_start);
        $end = Carbon::create($request->t_end);
        $day = Carbon::create($request->t_start)->dayOfWeek;

        // All Day Timeslot Creation
        if($timeslots == null){

            $timeslot = TemplateTimeslot::create([
                'start' => $start,
                'end' => $end,
                'duration' => 480,
                'quantity' => 0,
                'allDay' => true,
                'day' => $day,
                'court_template_id' => $request->template_id,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'blocked' => $request->blocked == 'on',
                'public_block' => $request->public_block == 'on',
                'block_reason' => $request->block_reason,
            ]);

            return true;
        }

        // Concurrent Timeslot Creation
        if($request->cattlecall === '1'){

            $timeslot = TemplateTimeslot::create([
                'start' => $start,
                'end' => $end,
                'day' => $day,
                'court_template_id' => $request->template_id,
                'duration' => $request->duration,
                'quantity' => $timeslots,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'blocked' => $request->blocked == 'on',
                'public_block' => $request->public_block == 'on',
                'block_reason' => $request->block_reason,
            ]);

            if(isset($request->timeslot_motions)){
                foreach($request->timeslot_motions as $motion){
                    TimeslotMotion::create([
                        'timeslotable_type' => 'App\Models\TemplateTimeslot',
                        'timeslotable_id' => $timeslot->id,
                        'motion_id' => $motion
                    ]);
                }
            }


            // Consecutive Timeslot Creation
        } else {
            $buffer = Carbon::create($request->t_start);

            // Loop for creation of consecutive timeslots
            for ($x = 0; $x < $timeslots; $x++){

                if($x > 0) {
                    $start->addMinutes($request->duration);
                }

                $buffer->addMinutes($request->duration);

                $timeslot = TemplateTimeslot::create([
                    'start' => $start,
                    'end' => $buffer,
                    'day' => $day,
                    'court_template_id' => $request->template_id,
                    'duration' => $request->duration,
                    'quantity' => 1,
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'blocked' => $request->blocked == 'on',
                    'public_block' => $request->public_block == 'on',
                    'block_reason' => $request->block_reason,
                ]);


                if(isset($request->timeslot_motions)){
                    foreach($request->timeslot_motions as $motion){
                        TimeslotMotion::create([
                            'timeslotable_type' => 'App\Models\TemplateTimeslot',
                            'timeslotable_id' => $timeslot->id,
                            'motion_id' => $motion
                        ]);
                    }
                }
            }
        }

        return response()->json(['success' => 'success'], 200);
    }


}
