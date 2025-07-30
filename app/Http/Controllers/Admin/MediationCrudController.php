<?php

namespace App\Http\Controllers\Admin;
use App\Models\EventType;
use App\Models\MediationCases;
use App\Models\MediationEvents;
use App\Models\Holiday;
use App\Models\MediationAvailableTimings;
use App\Models\MediationOutcome;
use App\Models\Judge;
use App\Models\Party;

use Illuminate\Support\Facades\Log;
use App\Models\County;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Library\Widget;
use ErrorException;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\MediationCaseConfirmation;
use App\Mail\MediationCaseCancellation;
use Illuminate\Support\Facades\Mail;

use App\Http\Requests\MediationRequest;

/**
 * Class MediationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MediationCrudController extends CrudController
{
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\MediationCases::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/mediation');
        CRUD::setEntityNameStrings('mediation', 'Civil Mediation');


        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->denyAccess(['revise']);
        }
        $this->crud->set('help',
            'All Attorney accounts live here. Attorneys must be enabled manually upon verification of bar number.'
        );


    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {

    }



    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {


    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setEditContentClass('col-md-12');
        $this->crud->setCreateContentClass('col-md-12');

        Widget::add()->type('script')->content('assets/js/admin/forms/mediation.js');

        CRUD::setValidation(MediationRequest::class);
        $print="";
        if ($this->crud->getCurrentOperation() === "update") {
            $print ='<a class="btn btn-primary" href="'.url('mediation/case/print/').'/'.request()->route('id').'" id="case_mediation_print" target="_blank">Case Mediation Print</a>';

            $print .='&nbsp;&nbsp;&nbsp;<a class="btn btn-primary" href="'.url('mediation/payments/').'/'.request()->route('id').'" id="case_mediation_payment" target="_blank">Case Payment</a>';

            $print .='&nbsp;&nbsp;&nbsp;<a class="btn btn-primary" href="'.route('email_instruction.show', request()->route('id')).'" id="email_instructions">Email Instructions</a>';
        }
            if(request()->has('case_number'))
            {
                $print .= "<p style='color:red;'>case number:<b>".request()->get('case_number')."</b> not found!<b></p>";
            }
            Widget::add()
            ->to('before_content')
            ->type('card')
            ->wrapper(['class' => 'form-group col-md-8'])
            ->content([

              'header' => 'Admin County Civil Mediation Editor',

              'body' => '
                <div class="card-body">
                  <div class="input-group">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Find Case / Create Case"
                      name="case_number_search"
                      id = "case_number_search"
                      value="'. request('case_number') .'"
                    >
                    <div class="input-group-append">
                      <button
                        class="btn btn-outline-primary"
                        onclick="searchCaseNumber()"
                      >
                        Search
                      </button>
                    </div>
                  </div>
                 <br> '. $print .'
                </div>',

            ]);


            CRUD::field('id')->type('hidden')->wrapper(['class' => 'hidden']);

            CRUD::field('c_caseno')->type('text')->label("Case Number")->wrapper(['class' => 'form-group col-md-4']);

            CRUD::field('c_div')->type('select2_from_ajax')->label('Division')
                ->entity('judge')->minimum_input_length(0)
                ->data_source('/api/judge')
                ->wrapper(['class' => 'form-group col-md-4']);

        if(request()->has('case_number') && substr(request()->case_number,0,2) == '05') {
            CRUD::addfield([   // select_from_array
                'name' => 'location_type_id',
                'label' => "Location Type",
                'type' => 'select_from_array',
                'options' => ['1' => 'Remote', '2' => 'In Person', '3' => 'Telephone',],
                'allows_null' => false,
                'default' => 'one',
                // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
                'wrapper' => [
                    'class' => 'form-group col-md-4'
                ]
            ]);
        } else{
            CRUD::addfield([   // select_from_array
                'name' => 'location_type_id',
                'label' => "Location Type",
                'type' => 'select_from_array',
                'options' => ['2' => 'In Person'],
                'allows_null' => false,
                'default' => 'one',
                // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
                'wrapper' => [
                    'class' => 'form-group col-md-4'
                ]
            ]);
        }

            CRUD::field('approved')->type('hidden')->value(true);
            CRUD::field('form_type')->type('hidden')->value('sc-form');

            CRUD::field('petitioner')->type('checkbox')->wrapper(['class' => 'form-group col-md-6 mt-4 mb-0 custom-form-group petcalculation'])->label("Indigent");
            CRUD::field('respondent')->type('checkbox')->wrapper(['class' => 'form-group col-md-6 mt-4 mb-0 custom-form-group rescalculation'])->label("Indigent");
            // Plaintiff Repeatable
            CRUD::addField([
                'name'  => 'attorneyParties',
                'label' => 'Plaintiffs',
                'type'  => 'repeatable',
                'entity' => 'attorneyParties', // the method that defines the relationship in your Model
                'model' => 'App\Models\Party', // foreign key model
                'subfields' => [ // also works as: "fields"
                    [
                        'name'    => 'name',
                        'type'    => 'text',
                        'label'   => 'Plaintiff\'s Name',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'    => 'type',
                        'type'    => 'hidden',
                        'value'   => 'plaintiff',
                    ],
                    [
                        'name'    => 'attorney_id',
                        'type'    => 'select2_from_ajax',
                        'label'   => 'Plaintiff\'s Attorney',
                        'entity'  => 'PltfAttroney',
                        'data_source' => '/api/attorney',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'    => 'telephone',
                        'type'    => 'text',
                        'label'   => 'Plaintiff\'s Phone',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],

                    [
                        'name'    => 'email',
                        'type'    => 'text',
                        'label'   => 'Plaintiff\'s Email',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'  => 'address',
                        'type'  => 'textarea',
                        'label' => 'Plaintiff\'s Address',
                    ],
                ],
                'wrapper' => ['class' => 'form-group col-md-6'],
                // optional
                'new_item_label'  => 'Add Another Party',
                'min_rows' => 1,
                'init_rows' => 1,
            ]);

            // Defendant Repeatable
            CRUD::addField([
                'name'  => 'defendantParties',
                'label' => 'Defendants',
                'type'  => 'repeatable',
                'entity' => 'defendantParties', // the method that defines the relationship in your Model
                'model' => 'App\Models\Party', // foreign key model
                'subfields' => [ // also works as: "fields"
                    [
                        'name'    => 'name',
                        'type'    => 'text',
                        'label'   => 'Defendant\'s Name',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'    => 'type',
                        'type'    => 'hidden',
                        'value'   => 'defendant',
                    ],
                    [
                        'name'    => 'attorney_id',
                        'type'    => 'select2_from_ajax',
                        'label'   => 'Defendant\'s Attorney',
                        'entity'  => 'DefAttroney',
                        'data_source' => '/api/attorney',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'    => 'telephone',
                        'type'    => 'text',
                        'label'   => 'Defendant\'s Phone',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],

                    [
                        'name'    => 'email',
                        'type'    => 'text',
                        'label'   => 'Defendant\'s Email',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'  => 'address',
                        'type'  => 'textarea',
                        'label' => 'Defendant\'s Address',
                    ],
                ],
                'wrapper' => ['class' => 'form-group col-md-6'],
                // optional
                'new_item_label'  => 'Add Another Party',
                'min_rows' => 1,
                'init_rows' => 1,
            ]);

            CRUD::field('c_type')->name('c_type')->label('Type')->inline(true)->type('radio')->options([
                'Auto Repair' => 'Auto Repair',
                'Breach of Contract' => 'Breach of Contract',
                'Consumer Goods' => 'Consumer Goods',
                'Eviction Resident' => 'Eviction Resident',
                'Eviction Commercial' => 'Eviction Commercial',
                'Recovery of Money' => 'Recovery of Money',
                'Worthless check' => 'Worthless check',
                'Other' => 'Other'
            ])->wrapper(['class' => 'form-group col-md-6 custom-form-group']);

            CRUD::field('c_otherm_text')->type('textarea')->label("Other note:")->wrapper(['class' => 'form-group col-md-6 custom-form-group']);
            CRUD::field('availability')->type('textarea')->label("Availability")->wrapper(['class' => 'form-group col-md-6 custom-form-group']);

            if ($this->crud->getCurrentOperation() === "update") {
            CRUD::field('approval_reason')->wrapper(['class' => 'form-group col-md-6 disabled'])->label("Approved Reason")->attributes(['readonly'=>'readonly']);
            }


            CRUD::field('c_cmmts')->type('textarea')->label("Comments")->wrapper(['class' => 'form-group col-md-12 custom-form-group']);


            // CRUD::field('c_sch_notes')->type('text')->label("Case Schedule Note")->wrapper(['class' => 'form-group col-md-12']);

            if ($this->crud->getCurrentOperation() === "update") {

                CRUD::field('Tb_sch_date')
                ->type('date')
                ->label("Schedule Date")
                ->wrapper(['class' => 'form-group col-md-4'])
                ->value(date('Y-m-d'))
                ->tab("Search");

                CRUD::field('Dd_time')
                ->name('Dd_time')
                ->inline(true)
                ->label("Time")
                ->type('select_from_array')
                ->options([
                    '09:30,09:30,10:00,10:30,11:00,11:30,12:00,12:30,13:00,13:30,14:00,14:30,15:00,15:30,16:00,16:30,17:00' => 'All Times',
                    '09:00' => '9:00 am',
                    '09:30' => '9:30 am',
                    '10:00' => '10:00 am',
                    '10:30' => '10:30 am',
                    '11:00' => '11:00 am',
                    '11:30' => '11:30 am',
                    '12:00' => '12:00 pm',
                    '12:30' => '12:30 pm',
                    '13:00' => '1:00 pm',
                    '13:30' => '1:30 pm',
                    '14:00' => '2:00 pm',
                    '14:30' => '2:30 pm',
                    '15:00' => '3:00 pm',
                    '15:30' => '3:30 pm',
                    '16:00' => '4:00 pm',
                    '16:30' => '4:30 pm',
                    '17:00' => '5:00 pm'
                ])
                ->wrapper(['class' => 'form-group col-md-4'])
                ->tab("Search");

                CRUD::field('separator')->type('custom_html')->value('<input type="button"  class="btn btn-primary searchEvents" style="margin-top:31px" value="Search" id="search" onClick="return searchEvents();">')->wrapper(['class' => 'form-group col-md-4'])->tab("Search");

                CRUD::field('searchEventResults')
                ->type('custom_html')
                ->value('<div class="searchEventResults table-responsive"></div>')
                ->wrapper(['class' => 'form-group col-md-12'])
                ->tab("Search");

            }


    }



    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function destroy($id, Request $request)
    {

    }

    /**
     * Update the specified resource in the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $request = $this->crud->validateRequest();

        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest($request)
        );
        $this->crud->setSaveAction();
        return $this->crud->performSaveAction($item->getKey());
    }

    public function searchCaseNumber(Request $request)
    {

        $case = MediationCases::where('c_caseno', 'LIKE', '%' . $request->case_number . '%')
            ->where('approved',1)
            ->where("form_type","sc-form")
            ->orderBy('id', 'desc')
            ->first();

        return $case;
    }

    public function searchEvents(Request $request)
    {

        $case = MediationEvents::with(['medmaster','case', 'outcome'])
            ->where('e_c_id',  $request->c_id)
            ->orderBy('id', 'desc')
            ->get();


        return $case;
    }



    public function availableTimings(Request $request)
    {
        $case = MediationCases::find($request->c_id);

        $county_code = substr($case->c_caseno, 0, 2);

        $county = '';

        if($county_code == '59'){
            $county = 'seminole';
        } else{
            $county = 'brevard';
        }

        $holiday = Holiday::where('date',$request->Tb_sch_date)->first();
        if(!isset($request->Tb_sch_date) || isset($holiday->date) || isset($holiday->c_id))
        {
            return [];
        }
        $Tb_sch_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->Tb_sch_date);

        $filterTimes = explode(",",$request->Dd_time);
        $formatedFilterTimes = [];

        foreach($filterTimes as $filterTime)
        {
            $formatedFilterTimes[] = $request->Tb_sch_date." ".$filterTime.":00";
        }

        $caseEvents = MediationAvailableTimings::select("mediation_avail_times.*")->with('medmaster')
        ->whereHas('medmaster', function ($query) use($county){
            $query->where('type', 'civil')->where('active', '1')->where('county', $county);
        })
        ->whereDate('at_begin', '<=' ,$Tb_sch_date )
        ->whereDate('at_end', '>=' ,$Tb_sch_date )
        ->whereIn('at_time',explode(",",$request->Dd_time))
        ->where('at_available',1)
        ->where('at_weekday',\Carbon\Carbon::createFromFormat('Y-m-d', $request->Tb_sch_date)->dayOfWeekIso)
        ->whereNotIn(DB::raw("concat(mediation_avail_times.at_time,':00-',mediation_avail_times.at_m_id)"), function ($query) use($request,$formatedFilterTimes) {
            $query->select(DB::raw('concat(time(e_sch_datetime),"-", e_m_id) as tt'))->from('mediation_events')
            // ->where('e_c_id',$request->c_id)
            ->whereIn('e_sch_datetime',$formatedFilterTimes);

        })
        ->whereNotIn(DB::raw("concat(mediation_avail_times.at_time,'-',mediation_avail_times.at_m_id)"), function ($query) use($request) {
            $query->select(DB::raw("concat(Dd_time,'-',Dd_med)"))->from('mediation_not_avail_times')
            ->whereDate('Tb_sdate','<=',\Carbon\Carbon::createFromFormat('Y-m-d', $request->Tb_sch_date))
            ->whereDate('Tb_edate','>=',\Carbon\Carbon::createFromFormat('Y-m-d', $request->Tb_sch_date))
            ->where('at_weekday',\Carbon\Carbon::createFromFormat('Y-m-d', $request->Tb_sch_date)->dayOfWeekIso);
        })
        ->orderBy('mediation_avail_times.id', 'desc')
        ->get();


        return $caseEvents;
    }

    public function eventStore(Request $request)
    {
        try{
            $schedule = MediationAvailableTimings::find($request->schedId);
            $case = MediationCases::find($request->caseId);
            $event = new MediationEvents;
            $event->e_c_id = $case->id;
            $event->e_m_id = $schedule->at_m_id;
            $event->e_pltf_a_id = $case->c_Pltf_a_id;
            $event->e_def_a_id = $case->c_def_a_id;
            $event->e_def_failedtoap = 0;
            $event->e_pltf_failedtoap = 0;
            $event->e_outcome_id = null;
            $event->e_sch_datetime = $request->Tb_sch_date." ".$schedule->at_time.":00";
            $event->e_sch_length = 0;
            $event->e_med_per_hr = 60;

            if($case->c_type == 'Eviction Resident' || $case->c_type == 'Eviction Commercial'){
                $event->e_med_fee = 0;
            } else{
                $event->e_med_fee = 120;
            }

            if(str_contains($case->case_num, 'SC')){
                $event->e_pltf_chg = 0;
                $event->e_def_chg = 0;
                $event->e_med_fee = 0;
            } else{
                if($case->petitioner || $case->c_type == 'Eviction Resident' || $case->c_type == 'Eviction Commercial'){
                    $event->e_pltf_chg = 0;
                } else{
                    $event->e_pltf_chg = 60;
                }

                if($case->respondent || $case->c_type == 'Eviction Resident' || $case->c_type == 'Eviction Commercial'){
                    $event->e_def_chg = 0;
                } else{
                    $event->e_def_chg = 60;
                }
            }

            // $event->e_subject = "";
            $event->e_notes = "";
            $event->save();
            return $event;
        }catch(ErrorException $e){
            return false;
        }

    }

    public function eventDelete(Request $request)
    {
        try{
            $event = MediationEvents::find($request->eventId);
            $event->delete();
            return true;
        }catch(ErrorException $e){
            return false;
        }

    }

    public function editEventSchedule($eventId)
    {
        try{
            $event = MediationEvents::with(['medmaster','case'])->where('id',$eventId)->first();
            $event->e_sch_datetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $event->e_sch_datetime)->format('m-d-Y h:i A');
            $event->e_sch_length = date("H:i", strtotime($event->e_sch_length));
            return $event;
        }catch(ErrorException $e){
            return $e;
        }
    }

    public function updateEventSchedule(Request $request)
    {
        try{
            $event = MediationEvents::find($request->eventId);
            $event->e_sch_length = $request->e_sch_length;
            $event->e_med_fee = $request->e_med_fee;
            $event->e_pltf_chg = $request->e_pltf_chg;
            $event->e_def_chg = $request->e_def_chg;
            $event->e_outcome_id = $request->e_outcome_id;
            $event->e_def_failedtoap = ($request->e_def_failedtoap != NULL) ? ($request->e_def_failedtoap == 'on' ? 1: 0) : 0;
            $event->e_pltf_failedtoap = ($request->e_pltf_failedtoap != NULL) ? ($request->e_pltf_failedtoap == "on" ? 1:0) : 0;
            // $event->e_subject = $request->e_subject;
            $event->e_notes = $request->e_notes;
            $event->save();
            return true;
        }catch(ErrorException $e){
            return false;
        }
    }

    public function outcomeList()
    {
        $outcome = MediationOutcome::get();
        return $outcome;
    }

    public function printCase($caseId)
    {
        $data['case'] = MediationCases::with(['events.medmaster','judge','PltfAttroney','DefAttroney'])
            ->where('id', $caseId)
            ->first();
        // dd($data);
        $data['caseType'] = [
                        'A' => 'Auto Repair',
                        'B' => 'Breach of Contract',
                        'C' => 'Consumer Goods',
                        'L' => 'Landlord',
                        'R' => 'Recovery of Money',
                        'W' => 'Worthless check',
                        'O' => 'Other'
                    ];

        return View::make('admin.case_print',$data);

    }

    function get_events($dbh,$icmsdb,$opencourtdb,$id,$ucn,$dbtype,$counties,$ccisucn) {
        switch ($dbtype) {
           case 'clericus':
              $events=get_events_clericus($dbh,$id);
              break;
           case 'crtv':
           case 'courtview':
              $events=get_events_courtview($dbh,$id);
              break;
           case 'facts':
              $events=get_events_facts($dbh,$id);
              break;
           case 'pioneer':
              $events=get_events_pioneer($dbh,$id);
              break;
           case 'new vision':
              $events=get_events_new_vision($dbh,$ucn,$id);
              break;
           case 'new_vision_replica':
              $events=get_events_new_vision($dbh,$ucn,$id);
              break;
           case 'showcase':
              $events=get_events_showcase();
              break;
           case 'odyssey':
              $events=get_events_odyssey($id);
              break;
           case 'cscribe':
              $events=get_events_cscribe($id, $ucn);
              break;
           case 'jis':
              $events=get_events_jis($ucn);
              break;
           default:
             echo "get_events: Unsupported dbtype $dbtype for $id\n";
             exit(1);
         }
        if (db_exists("circuit8")) { # ICMS classic - pull old events (I'm assuming no other events entered, so I'm not bothering with a sort)
            $circuit8db=db_connect("circuit8");
            $oldevents=sqlarrayp($circuit8db,"select to_char(edate,'MM/DD/YYYY'),to_char(estart,'HH:MI am'),dscr,eloc from events, eventtypes where casenum=? and events.etype=eventtypes.etype order by edate desc",array($ucn));
            foreach ($oldevents as $x) {
               $x[2].="<span style='font-size:8pt'><i>(ICMS)</i></span>";
               $events[]=$x;
            }
        }
        # now pull events from JACS calendar
        if (db_exists("jacs")) { # JACS - Brevard and possibly elsewhere
          $jacsdb=db_connect("jacs");
          $ucns=$ucn.'%';
          $ucns2=substr($ucn,3).'%';
          $q ="SELECT
                  replace(convert(varchar,CALDATE,110),'-','/'),
                  timefrom,
                  templatedesc,
                  TBCourtrooms.description,
                  TBMOTIONS.DESCRIPTION
               FROM TBCOURTCALENDAR
               LEFT JOIN TBCourtrooms ON
                  TBCOURTCALENDAR.courtroomid = TBCourtrooms.Courtroom_id
               LEFT JOIN TBMOTIONS ON
                  TBCOURTCALENDAR.MOTIONCODE=TBMOTIONS.MOTIONCODE
               WHERE (CASENUM LIKE  ? OR CASENUM LIKE ?)
                  AND DIVISION_ID NOT IN ('A','W','L','E','J','M','K','G','P',
                     'B','R','Z','F','C','S','U','CF_J','Cr_F','Crim_C','FF')
               ORDER BY CALDATE DESC";
          $jacsevts=sqlarrayp($jacsdb,$q,array($ucns,$ucns2));
          logerr("get_events: jacs: ".count($jacsevts)." found...");
          for ($i=0;$i<count($jacsevts);$i++) {
              list($dt,$tm,$desc,$loc,$desc2)=$jacsevts[$i];
              $tm=pretty_hhmm($tm);
              $desc.="$desc2 <span style='font-size:8pt'><i>(JACS)</i></span>";
              $events[]=array($dt,$tm,$desc,$loc);
          }
        }
        # now pull events from ICMS calendar
        $icmsevts=sqlarrayp($icmsdb," select date_format(event_dt,'%m/%d/%Y'),date_format(event_start_tm,'%h:%i %p'),dscr,loc_dscr,cancelled from calendar_icms where ucn=?",array($ucn));
        for ($i=0;$i<count($icmsevts);$i++) {
            list($dt,$tm,$desc,$loc,$status)=$icmsevts[$i];
            if ($status==1) { $status="CANCELLED"; }
            else { $status=""; }
            $desc.=" <span style='font-size:8pt'><i>(ICMS)</i></span>";
            $events[]=array($dt,$tm,$desc,$loc,$status);
        }
        # now pull events from OpenCourt calendar
        # (if opencourt_ip set for this county)
        $ocevents=get_OC_events($ucn,$counties);
        foreach ($ocevents as $x) {
           $fname=$x->{filename};
           $ocip=$x->{oc_ip};
           list($fdate,$ftime,$fbld,$froomext)=explode("~",$fname);
           list($froom,$ext)=explode(".",$froomext);
           $fdate=pretty_date($fdate);
           $filepos=$x->{filepos};
           $duration=$x->{duration};
           $ocevtype=$x->{eventtype};
           if ($ocevtype=="") { $ocevtype="Event"; }
           $ocevtype.=" (OC)";
           $ocdesc="<span class=oclink ocip='$ocip' ocfname='$fname' ocpos='$filepos' ocdur='$duration'><img src=/icms/icons/octiny.jpg> $ocevtype</span>";
           $x=array($fdate,$ftime,$ocdesc,"$fbld-$froom");
           $events[]=$x;
        }
        $events=sort_events($events);

        return $events;
     }

     public function scFormList()
     {
        $judges = Judge::get();
        $counties = County::get();
         $event_types = EventType::get();
        $cases = MediationCases::with(['judge','PltfAttroney','DefAttroney','parties.attorney'])->where("form_type","!=",NULL)->where("approved",0)->get();
        return view('admin.sc_form_list',
            [
                'cases' => $cases,
                'judges' => $judges,
                'counties' => $counties,
                'event_types' => $event_types
            ]);
     }

     public function scFormApprove(Request $request)
     {

        $rules = [
            'case_num' => 'required',
            'judge' => 'required',
            // 'plaintiff' => 'required',
            // 'defendant' => 'required',
            'party.0.name' => 'required',
            'party.1.name' => 'required',
            'p_signature' => 'bail|required_if:d_signature,null|prohibited_unless:d_signature,null',
            'd_signature' => 'required_if:p_signature,null|prohibited_unless:p_signature,null',
            'defendant_email.*' => 'email:rfc,dns',
            'plaintiff_email.*' => 'email:rfc,dns'
        ];

        $messages = [
            'prohibited_unless' => 'You must Sign in either the Plaintiff or Defendant at the bottom of the form.',
            'required_if' => 'You must Sign in either the Plaintiff or Defendant at the bottom of the form.',
            'case_num.required' => 'The Case Number field is required.',
            'plaintiff_email.*' => 'One or Many Plaintiffs Email Address are Invalid',
            'defendant_email.*' => 'One or Many Defendant Email Address are Invalid'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->stopOnFirstFailure()->validate();

        $case  = MediationCases::find($request->mediation_case_id);
        $case->c_caseno = $request->case_num;
        $case->c_div = $request->judge;
        $case->c_type = $request->type;
        $case->petitioner = $request->petitioner;//
        $case->respondent = $request->respondent;//
        // $case->c_pltf_name = $request->plaintiff;
        // $case->c_Pltf_a_id = $request->plaintiff_att;
        // $case->c_pltf_address = $request->plaintiff_add;
        // $case->c_pltf_phone = $request->plaintiff_tel;
        // $case->c_pltf_email = $request->plaintiff_email;
        // $case->c_def_name = $request->defendant;
        // $case->c_def_a_id = $request->defendant_att;
        // $case->c_def_address = $request->defendant_add;
        // $case->c_def_phone = $request->defendant_tel;
        // $case->c_def_email = $request->defendant_email;
        if($case->form_type == 'sc-form'){
            $case->gal = $request->gal;//
            $case->gal_tel = $request->gal_tel;//
            $case->gal_add = $request->gal_add;//
            $case->gal_email = $request->gal_email;//
            // $case->sc_issues = implode(",",array_values($request->sc_issues));//
            $case->f_issues = is_null($request->f_issues) ? null : implode(",", array_values($request->f_issues));
            $case->f_issues_other_notes = $request->f_issues_other_notes;
        }
        $case->previous = $request->previous;//
        $case->previous_case_num = $request->previous_case_num;//
        $case->origin = $request->origin;//
        $case->previous_case_tel = $request->previous_case_tel;//
        $case->previous_case_email = $request->previous_case_email;//
        $case->availability = $request->availability;//
        $case->p_signature = $request->p_signature;//
        $case->d_signature = $request->d_signature;//
        // $case->sc_form = 1;//
        $case->approved = 1;
        $case->approval_reason = $request->approval_reason;
        $case->save();

        foreach ($request->party as $partyDetails) {
            $party = Party::find($partyDetails['id']);
            $party->name = $partyDetails['name'];
            $party->attorney_id = $partyDetails['attorney_id'];
            $party->telephone = $partyDetails['telephone'];
            $party->email = $partyDetails['email'];
            $party->address = $partyDetails['address'];
            $party->save();
        }

        $case = MediationCases::with(['judge','PltfAttroney','DefAttroney'])->where("id", $request->mediation_case_id)->first();

        $toEmails = [];

        if(isset($case->PltfAttroney->email) && !empty($case->PltfAttroney->email))
        {
            foreach($case->PltfAttroney->email as $email){
                $toEmails[] = $email->email;
            }
        }

        if(isset($case->DefAttroney->email) && !empty($case->DefAttroney->email))
        {
            foreach($case->DefAttroney->email as $email){
                $toEmails[] = $email->email;
            }
        }

        if(!empty($request->plaintiff_email))
        {
            $toEmails = array_merge($toEmails,explode(";",$request->plaintiff_email));
        }
        if(!empty($request->defendant_email))
        {
            $toEmails = array_merge($toEmails,explode(";",$request->defendant_email));
        }

        // echo json_encode($toEmails);exit;
        if(!empty(array_unique($toEmails))){
            foreach(array_unique($toEmails) as $email){
                Mail::to($email)->send(new MediationCaseConfirmation($case));
                Log::debug($email);
            }
        }
        // return $toEmails;
        return $case;
     }

     public function scFormDelete(Request $request)
     {
        $case = MediationCases::find($request->sc_id);
        $toEmails = [];

        if(isset($case->PltfAttroney->email) && !empty(trim($case->PltfAttroney->email)))
        {
            foreach($case->PltfAttroney->email as $email){
                $toEmails[] = $email->email;
            }
        }

        if(isset($case->DefAttroney->email) && !empty(trim($case->DefAttroney->email)))
        {
            foreach($case->DefAttroney->email as $email){
                $toEmails[] = $email->email;
            }
        }

        if(!empty(trim($case->c_pltf_email)))
        {
            $toEmails = array_merge($toEmails,explode(";",$case->c_pltf_email));
        }
        if(!empty(trim($case->c_def_email)))
        {
            $toEmails = array_merge($toEmails,explode(";",$case->c_def_email));
        }

        if(!empty(array_unique($toEmails))){
            foreach(array_unique($toEmails) as $email){
                Mail::to($email)->send(new MediationCaseCancellation($case));
                Log::debug($email);
            }
        }

        $case->cancel_reason = $request->cancel_reason;
        $case->save();
        $case->delete();

        return true;
     }

}

