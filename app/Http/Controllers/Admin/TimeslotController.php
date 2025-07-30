<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeslotRequest;
use App\Mail\HearingRescheduling;
use App\Models\Court;
use App\Models\CourtPermission;
use App\Models\CourtTimeslot;
use App\Models\Template;
use App\Models\TemplateTimeslot;
use App\Models\Timeslot;
use App\Models\TimeslotMotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TimeslotController extends Controller
{
    public function update(TimeslotRequest $request, Timeslot $timeslot){

        if($request->t_start == null){
            $start = Carbon::create($request->start)->timezone('America/New_York');
            $end = Carbon::create($request->end)->timezone('America/New_York');
            $timeslot->duration = $start->diffInMinutes($end);

        } else{
            $start = $request->t_start;
            $end = $request->t_end;
            $timeslot->description = $request->description;
            $timeslot->category_id = $request->category_id;
            $timeslot->quantity = $request->quantity;
            $timeslot->duration = $request->duration;
            $timeslot->blocked = $request->blocked == 'on';
            $timeslot->public_block = $request->blocked != 'on' ? false : $request->public_block == 'on';
            $timeslot->block_reason = $request->block_reason;
            //$timeslot->motions()->sync($request->timeslot_motions);

            if(empty($request->timeslot_motions)){
                TimeslotMotion::where('timeslotable_id', $timeslot->id)->delete();
            } else{
                $old_restrcited_motions = TimeslotMotion::where('timeslotable_id', $timeslot->id)->get();

                foreach($old_restrcited_motions as $motion){
                    if(!in_array($motion->id, $request->timeslot_motions)){
                        $motion->delete();
                    }
                }

                foreach($request->timeslot_motions as $motion){
                    TimeslotMotion::firstOrCreate([
                        'timeslotable_id' => $timeslot->id,
                        'timeslotable_type' => 'App\Models\Timeslot',
                        'motion_id' => $motion
                    ]);
                }
            }

//            if($timeslot->court->email_confirmations){
//
//                $emailFind=self::array_key_exists_wildcard($event->templates_data, '*_|EMAIL', 'key-value');
//                $toEmails = [];
//                if(isset($event->attorney->email))
//                {
//                    $toEmails[] = $event->attorney->email;
//                }
//                if(isset($event->opp_attorney->email))
//                {
//                    $toEmails[] = $event->opp_attorney->email;
//                }
//
//                if(!empty($event->plaintiff_email))
//                {
//                    foreach($event->plaintiff_email as $email){
//                        $toEmails[] = $email;
//                    }
//                }
//                if(!empty($event->defendant_email))
//                {
//                    foreach($event->defendant_email as $email){
//                        $toEmails[] = $email;
//                    }
//                }
//                foreach($event->timeslot->court->judge->ja as $ja){
//                    $toEmails[] = $ja->email;
//                }
//
//                if(!empty($emailFind) && count($emailFind) > 0 ){
//                    $toEmails=array_merge( $emailFind, $toEmails);
//                }
//
//                foreach(array_unique($toEmails) as $email){
//                    Mail::to($email)->send(new HearingRescheduling($event, $old));
//                }
//            }
        }

        $timeslot->start = $start;
        $timeslot->end = $end;

        $timeslot->save();

        return response()->json(['success' => 'success'], 200);
    }

    public function edit(Timeslot $timeslot){
        return Timeslot::with(['events','events.attorney','events.motion','events.opp_attorney','events.ownerable','motions'])->find($timeslot->id);
    }

    public function destroy(Timeslot $timeslot){

        $court_timeslot = CourtTimeslot::where('timeslot_id',$timeslot->id)->first();

        $court = Court::find($court_timeslot->court_id);

        $permission = CourtPermission::where('judge_id', $court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();

        if(backpack_user()->hasRole(['System Admin'])){

            foreach($timeslot->motions as $motion){
                $motion->delete();
            }

//            $court_timeslot = CourtTimeslot::where('timeslot_id', $timeslot->id)->first();
//            $court_timeslot->delete();
            $timeslot->delete();

            return response()->json(['success' => 'success'], 200);
        }

        if(!$permission->editable){
            abort(403);
        }

        foreach($timeslot->motions as $motion){
            $motion->delete();
        }
        // Time slot should not be removed from court due to soft delete on timeslot
//        $court_timeslot = CourtTimeslot::where('timeslot_id', $timeslot->id)->first();
//        $court_timeslot->delete();
        $timeslot->delete();

        return response()->json(['success' => 'success'], 200);
    }

    public function destroy_multi(Request $request){

        $timeslots = $request->all();

        foreach ($timeslots as $timeslot){

            $court_timeslot = CourtTimeslot::where('timeslot_id',$timeslot)->first();

            $court = Court::find($court_timeslot->court_id);

            $timeslot_data = Timeslot::find($timeslot);

            $permission = CourtPermission::where('judge_id', $court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();

            if(backpack_user()->hasRole(['System Admin'])){

                foreach($timeslot_data->motions as $motion){
                    $motion->delete();
                }

                $timeslot_data->delete();

                continue;
            }

            if(!$permission->editable){
                abort(403);
            }

            foreach($timeslot_data->motions as $motion){
                $motion->delete();
            }

            // Time slot should not be removed from court due to soft delete on timeslot
            $timeslot_data->delete();

        }

        return response()->json(['success' => 'success'], 200);
    }

    public function template_destroy_multi(Request $request){

        $timeslots = $request->all();

        foreach ($timeslots as $timeslot){

            $court_timeslot = TemplateTimeslot::where('id',$timeslot)->first();

            $template = Template::find($court_timeslot->court_template_id);

            $permission = CourtPermission::where('judge_id', $template->court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();

            if(backpack_user()->hasRole(['System Admin'])){


                foreach($court_timeslot->motions as $motion){
                    $motion->delete();
                }

                $court_timeslot->delete();

                continue;
            }

            if(!$permission->editable){
                abort(403);
            }

            foreach($court_timeslot->motions as $motion){
                $motion->delete();
            }

            // Time slot should not be removed from court due to soft delete on timeslot
            $court_timeslot->delete();

        }

        return response()->json(['success' => 'success'], 200);
    }

    public function temp_copy(Request $request){

        $timeslots = $request->all();

        foreach ($timeslots as $timeslot){

            $court_timeslot = TemplateTimeslot::where('id',$timeslot)->first();

            $template = Template::find($court_timeslot->court_template_id);

            $permission = CourtPermission::where('judge_id', $template->court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();

            if(backpack_user()->hasRole(['System Admin'])){

                $copy = $court_timeslot->replicate();

                $copy->save();

                foreach($court_timeslot->motions as $motion){
                    $motion_copy = $motion->replicate()->fill([
                        'timeslotable_id' => $copy->id
                    ]);

                    $motion_copy->save();
                }
                continue;
            }

            if(!$permission->editable){
                abort(403);
            }

            $copy = $court_timeslot->replicate();

            $copy->save();

            foreach($court_timeslot->motions as $motion){
                $motion->replicate()->fill([
                    'timeslotable_id' => $copy->id
                ]);
            }

        }

        return response()->json(['success' => 'success'], 200);
    }

    public function copy(Request $request){

        $timeslots = $request->all();

        foreach ($timeslots as $timeslot){

            $court_timeslot = CourtTimeslot::where('timeslot_id',$timeslot)->first();

            $court = Court::find($court_timeslot->court_id);

            $timeslot_data = Timeslot::find($timeslot);

            $permission = CourtPermission::where('judge_id', $court->judge->id ?? null)->where('user_id',backpack_user()->id)->first();


            if(backpack_user()->hasRole(['System Admin'])){

                $copy = $timeslot_data->replicate();

                $copy->save();

                foreach($timeslot_data->motions as $motion){
                    $motion_copy = $motion->replicate()->fill([
                        'timeslotable_id' => $copy->id
                    ]);

                    $motion_copy->save();
                }

                CourtTimeslot::create([
                    'court_id' => $court_timeslot->court_id,
                    'timeslot_id' => $copy->id
                ]);


                continue;
            }

            if(!$permission->editable){
                abort(403);
            }

            $copy = $timeslot_data->replicate();

            $copy->save();

            foreach($timeslot_data->motions as $motion){
                $motion_copy = $motion->replicate()->fill([
                    'timeslotable_id' => $copy->id
                ]);

                $motion_copy->save();
            }

            CourtTimeslot::create([
                'court_id' => $court_timeslot->court_id,
                'timeslot_id' => $copy->id
            ]);

        }

        return response()->json(['success' => 'success'], 200);
    }
    public function store(TimeslotRequest $request){

        $timeslots = $request->quantity;
        $start = Carbon::create($request->t_start);
        $end = Carbon::create($request->t_end);

        // All Day Timeslot Creation
        if($timeslots == null){

            $timeslot = Timeslot::create([
                'start' => $start,
                'end' => $end,
                'duration' => 480,
                'quantity' => 0,
                'allDay' => true,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'blocked' => $request->blocked == 'on',
                'public_block' => $request->public_blocked == 'on',
                'block_reason' => $request->block_reason,
            ]);

            CourtTimeslot::create([
                'court_id' => $request->court_id,
                'timeslot_id' => $timeslot->id
            ]);

            return true;
        }

        // Concurrent Timeslot Creation
        if($request->cattlecall === '1'){

            $timeslot = Timeslot::create([
                'start' => $start,
                'end' => $end,
                'duration' => $request->duration,
                'quantity' => $timeslots,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'blocked' => $request->blocked == 'on',
                'public_block' => $request->public_block == 'on',
                'block_reason' => $request->block_reason,
            ]);

            CourtTimeslot::create([
                'court_id' => $request->court_id,
                'timeslot_id' => $timeslot->id
            ]);

            if(isset($request->timeslot_motions)){
                foreach($request->timeslot_motions as $motion){
                    TimeslotMotion::create([
                        'timeslotable_id' => $timeslot->id,
                        'timeslotable_type' => 'App\Models\Timeslot',
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

                $timeslot = Timeslot::create([
                    'start' => $start,
                    'end' => $buffer,
                    'duration' => $request->duration,
                    'quantity' => 1,
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'blocked' => $request->blocked == 'on',
                    'public_block' => $request->public_blocked == 'on',
                    'block_reason' => $request->block_reason,
                ]);

                CourtTimeslot::create([
                    'court_id' => $request->court_id,
                    'timeslot_id' => $timeslot->id
                ]);

                if(isset($request->timeslot_motions)){
                    foreach($request->timeslot_motions as $motion){
                        TimeslotMotion::create([
                            'timeslotable_id' => $timeslot->id,
                            'timeslotable_type' => 'App\Models\Timeslot',
                            'motion_id' => $motion
                        ]);
                    }
                }
            }
        }

        return response()->json(['success' => 'success'], 200);
    }


}
