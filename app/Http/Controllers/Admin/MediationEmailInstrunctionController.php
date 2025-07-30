<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MediationCaseConfirmation;
use App\Mail\MediationEmailInstructions;
use App\Models\Category;
use App\Models\County;
use App\Models\Court;
use App\Models\MediationCases;
use App\Models\MediationEvents;
use App\Models\MediationInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Prologue\Alerts\Facades\Alert;

class MediationEmailInstrunctionController extends Controller
{
    public function show(Request $request){

        $case = MediationCases::find($request->case_id);

        $county = substr($case->c_caseno,0,2) == '59' ? 'Seminole' : 'Brevard';
        $county_id = County::where('name', $county)->first()->id;
        $location_type_id = $case->location_type_id;
        $case_type = $case->form_type == 'f-form' ? 'family' : 'civil';

        $instructions = MediationInstruction::where('county_id', $county_id)
            ->where('location_type_id', $location_type_id)->where('case_type', $case_type)->first();

        $parties = $case->parties;

        return view('admin.mediation.email_instructions',
            ['instructions' => $instructions, 'case' => $case, 'parties' => $parties->groupBy('type')]);
    }

    public function emailInstructions(Request $request){

        $case = MediationCases::find($request->case_id);

        $parties = $case->parties;

        foreach ($parties as $party) {
            if($party->email !== null){
                Mail::to($party->email)->send(new MediationEmailInstructions($case, $request->instructions));
            }
        }

        foreach(explode(';', $request->emails) as $email){

            $validator = Validator::make(['email' => $email],[
                'email' => 'email'
            ]);

            if($validator->passes()){
                Mail::to($email)->send(new MediationEmailInstructions($case, $request->instructions));
            }
        }

        Alert::add('success', 'Instructions have been sent to all party members!')->flash();

        return redirect()->route('mediation.edit', $case->id);
    }
}
