<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediationEvents;
use App\Models\MediationCases;
use App\Models\MediationMediator;
use App\Models\MediationOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Response;

class MediationReportsController extends Controller
{
    public function index()
    {
        $mediators = MediationMediator::get();
        return view('admin.weekreport',
                [
                    "mediators" => $mediators
                ]);
    }

    public function searchReport(Request $request)
    {
        $events = MediationEvents::with(['medmaster','case.PltfAttroney','case.DefAttroney','payments'])
                    ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)
                        ->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")]);
        if($request->mediator_id != ""){
            $events = $events->where('e_m_id',$request->mediator_id);
        }
        if($request->form_type != ""){
            $events = $events->whereHas('case', function($q) use($request){
                $q->where('form_type', $request->form_type);
             });
        }
        if($request->county != ""){
            $events = $events->whereHas('case', function($q) use($request){
                $q->where('c_caseno', 'like', $request->county . '%');
            });
        }
        $events = $events->orderBy('e_sch_datetime',"ASC")->get();


        $report = "";
        $loopDate = "";
        foreach($events as $event)
        {
            $first_party_members_attorneys = '';
            $second_party_members_attorneys = '';

            $grouped_parties = $event->case->parties->groupBy('type');

            $first_party_type = $grouped_parties->keys()[0];
            $second_party_type = $grouped_parties->keys()[1];

            $first_party_members = $grouped_parties[$first_party_type]->implode('name', ', ');
            $second_party_members = $grouped_parties[$second_party_type]->implode('name', ', ');

            foreach ($grouped_parties[$first_party_type] as $party) {
                if(isset($party->attorney)){
                    $first_party_members_attorneys .= $party->attorney->name . ' ';
                }
            }

            foreach ($grouped_parties[$second_party_type] as $party) {
                if(isset($party->attorney)){
                    $second_party_members_attorneys .= $party->attorney->name . ' ';
                }
            }


            if($loopDate == "" || $loopDate != \Carbon\Carbon::parse($event->e_sch_datetime)->format('l n/j/Y'))
            {
                $loopDate = \Carbon\Carbon::parse($event->e_sch_datetime)->format('l n/j/Y');
                $report .= "<h3 class='col-md-12'>".\Carbon\Carbon::parse($event->e_sch_datetime)->format('l n/j/Y')."</h3><br>";
            }
            $report .= "<table class='col-md-12 table table-bordered text-center'>";
            $report .= "<tr><td colspan='4'><b>".\Carbon\Carbon::parse($event->e_sch_datetime)->format('h:i:s A l, F j, Y')."</b></td></tr>";
            $report .= "<tr><td>Case No</td><td>Mediator</td><td>" . Str::title($first_party_type) . "</td><td>" . Str::title($second_party_type) . "</td></tr>";
            $report .= "<tr><td>".$event->case->c_caseno."</td><td>" . $event->medmaster->name . "</td><td>" . $first_party_members ."</td><td>" . $second_party_members . "</td></tr>";
            $report .= "<tr><td></td><td class='text-right'>Attys:</td><td>" . $first_party_members_attorneys . "</td><td>" . $second_party_members_attorneys . "</td></tr>";
            $report .= "<tr><td></td><td></td><td>Invoice: ".$event->e_pltf_chg." Paid:" . $event->payments->where("paid_by",Str::title($first_party_type))->sum("amount_paid")."</td><td>Invoice: ".$event->e_def_chg." Paid:" . $event->payments->where("paid_by",Str::title($second_party_type))->sum("amount_paid")."</td></tr>";
            $report .= "<tr class='text-left'><td colspan='4'>START TIME ".\Carbon\Carbon::parse($event->e_sch_datetime)->format('h:i')."</td></tr>";

            if(isset($event->e_notes)){
                $report .= "<tr class='text-left'><td colspan='4'>Notes: " . $event->e_notes . "</td></tr>";
            }

            if(isset($event->case->c_cmmts)){
                $report .= "<tr class='text-left'><td colspan='4'>Case Comments: " . $event->case->c_cmmts . "</td></tr>";
            }

            $report .= "</table>";
        }

        return $report;
    }

    public function countyStatsReport()
    {
        return view('admin.countystats');
    }

    public function getCountyStats(Request $request)
    {
        if($request->form_type == 'f-form'){
            $sessions = [
                'Divorce with Children' => 'Divorce with Children',
                'Divorce without Children' => 'Divorce without Children',
                'Paternity' => 'Paternity',
                'Modification' => 'Modification'
            ];
        }
        else{
            $sessions = [
                'Auto Repair' => 'Auto Repair',
                'Breach of Contract' => 'Breach of Contract',
                'Consumer Goods' => 'Consumer Goods',
                'Eviction Resident' => 'Eviction Resident',
                'Eviction Commercial' => 'Eviction Commercial',
                'Recovery of Money' => 'Recovery of Money',
                'Worthless check' => 'Worthless check',
                'Other' => 'Other'
            ];
        }
        $cases = MediationCases::select("c_type", DB::raw("count(*) as count"))
                ->whereBetween(DB::raw("date(created_at)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
                ->where('form_type',$request->form_type)
                ->groupBy("c_type")
                ->get();



        $events = MediationOutcome::with(['events' => function ($query) use($request) {
                    $query = $query->select(DB::raw("count(id) as count"),'e_outcome_id');
                    $query = $query->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")]);
                    $query = $query->whereHas('case', function ($query) use($request){
                        $query->where('form_type', $request->form_type);
                    });
                    $query = $query->groupBy("e_outcome_id");
                    }])->get();

        // $events = MediationEvents::with(['outcome' => function ($query) {
        //                 $query->select('*');
        //             }])
        //             ->select(DB::raw("count(id) as count"),'e_outcome_id')
        //             ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
        //             ->groupBy("e_outcome_id")
        //             ->get();

                    // return json_encode($events);

        $report = '<div class="row pdfheading col-md-12">
                        <p class="text-center"><b>County Stats  ~  '.\Carbon\Carbon::parse($request->date_from)->format("m-d-Y").' to '.\Carbon\Carbon::parse($request->date_to)->format("m-d-Y").'</b></p>
                    </div>
                    <br>';

        $case_report = '<table class="table table-sm col-md-6">
                        <tr><td><b>TYPES OF SESSIONS</b></td><td><b>COUNT</b></td></tr>';

        // return $cases->where('c_type',"A")->first()->count;
        $total_count = 0;
        foreach($sessions as $index => $value)
        {
            if($cases->where('c_type',$index)->first() != null){
                $total_count += (($cases->where('c_type',$index)->first()->count) ? $cases->where('c_type',$index)->first()->count : 0);
                $case_report .= '<tr><td>'.$value.'</td><td>'.(($cases->where('c_type',$index)->first()->count) ? $cases->where('c_type',$index)->first()->count : 0).'</td></tr>';
            }
        }

        $case_report .= '<tr><td><b> Total: <b></td><td>'.$total_count.'</td></tr>';

        $case_report .= "</table><br><br><br>";

        $event_report = '<table class="table table-sm col-md-6">
                        <tr><td><b>CASE OUTCOME</b></td><td><b>COUNT</b></td></tr>';

        $events_total = 0;
        foreach($events as $event)
        {
            $events_total +=(isset($event->events[0]->count) ? $event->events[0]->count : 0);
            $event_report .= '<tr><td>'.$event->o_outcome.'</td><td>'.(isset($event->events[0]->count) ? $event->events[0]->count : 0).'</td></tr>';
        }
        $event_report .= '<tr><td><b>Total: </b></td><td>'.$events_total.'</td></tr>';
        $event_report .= "</table>";

        $report .= $case_report.$event_report;
        return $report;
    }

    public function mediatorReport()
    {
        $mediators = MediationMediator::get();
        return view('admin.mediatorstats',
        [
           "mediators" => $mediators
        ]);
    }

    public function getMediatorStats(Request $request)
    {


        if($request->mediator_id == 'all'){
            $events = MediationEvents::with(['medmaster','case'])
                ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
                ->get();
        } elseif($request->mediator_id == 'brevard'){
            $mediator = MediationMediator::where('county', 'brevard')->get();

            $events = MediationEvents::with(['medmaster','case'])
                ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
                ->wherein('e_m_id', $mediator->pluck('id'))
                ->get();
        } elseif($request->mediator_id == 'seminole'){
            $mediator = MediationMediator::where('county', 'seminole')->get();

            $events = MediationEvents::with(['medmaster','case'])
                ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
                ->wherein('e_m_id', $mediator->pluck('id'))
                ->get();
        }  else{
            $events = MediationEvents::with(['medmaster','case'])
                ->whereBetween(DB::raw("date(e_sch_datetime)"), [\Carbon\Carbon::parse($request->date_from)->format("Y-m-d"), \Carbon\Carbon::parse($request->date_to)->format("Y-m-d")])
                ->where('e_m_id',$request->mediator_id)
                ->get();
        }


                    // return json_encode($events);

        $report = '<div class="row pdfheading col-md-12">
                        <p class="text-center"><b>Mediator Services Provided  ~  '.\Carbon\Carbon::parse($request->date_from)->format("m-d-Y").' to '.\Carbon\Carbon::parse($request->date_to)->format("m-d-Y").'</b></p>
                    </div>
                    <br>';


        $event_report = '<table class="table table-sm col-md-12">
                        <tr><td>Scheduled</td><td>Case No</td><td>Mediator</td><td>Length</td><td>Outcome</td><td>Amount</td><td>Subject</td><td>Payment</td></tr>';

        $events_total = $length = 0;
        foreach($events as $event)
        {

            $mediation_fee = $event->e_med_fee - ($event->case->petitioner * ($event->e_med_fee/2)) - ($event->case->respondent * ($event->e_med_fee/2));
            $payment_status = ($event->payments->sum('amount_paid') == $mediation_fee) ? 'Paid' : 'Not Paid';


            $events_total += (is_numeric($event->e_pltf_chg) ? $event->e_pltf_chg : 0) + (is_numeric($event->e_def_chg) ? $event->e_def_chg : 0);
            $length += is_numeric($event->e_sch_length) ? $event->e_sch_length : 0;
            $event_report .= '<tr><td>'.\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $event->e_sch_datetime)->format('m-d-Y h:i A').'</td>
                                <td>'.$event->case->c_caseno.'</td>
                                <td>'.@$event->medmaster->name.'</td>
                                <td>'.date("H:i", strtotime($event->e_sch_length)).'</td>
                                <td>'.@$event->outcome->o_outcome.'</td>
                                <td>$ '.number_format($event->e_pltf_chg + $event->e_def_chg,2).'</td>
                                <td>'.$event->e_subject.'</td><td>' . $payment_status . '</td></tr>';
        }
        $event_report .= '<tr><td></td><td></td><td><b>Length</b></td><td>'.$length.'</td><td><b>Total: </b></td><td>$'.number_format($events_total,2).'</td></tr>';
        $event_report .= "</table>";

        $report .= $event_report;
        return $report;
    }
}
