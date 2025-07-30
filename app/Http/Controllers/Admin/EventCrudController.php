<?php

namespace App\Http\Controllers\Admin;
use App\Models\Court;
use App\Http\Requests\EventRequest;
use App\Mail\HearingCancellation;
use App\Mail\HearingConfirmation;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\TimeslotEvent;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\ReviseOperation\ReviseOperation;
use App\Models\Motion;
use App\Models\Attorney;
use App\Models\EventReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Prologue\Alerts\Facades\Alert;
use App\Models\CourtPermission;


/**
 * Class EventCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EventCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Event::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/event');
        CRUD::setEntityNameStrings('event', 'events');

        $this->crud->denyAccess(['create']);

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin',"JA"])){
            $this->crud->denyAccess(['revise']);
        }

        if(backpack_user()->hasRole([ 'JA'])){
            $this->crud->addClause('whereHas','timeslot.court', function($query){
               $query->whereIn('court_id', backpack_user()->courts());
            });
        }

        $this->crud->addFilter([
            'name'  => 'court',
            'type'  => 'dropdown',
            'label' => 'Court'
        ], backpack_user()->courtsFilter(), function($value) { // if the filter is active
            $this->crud->addClause('whereHas','timeslot.court', function($query) use ($value){
                $query->where('court_id', $value);
            });
        });

        $this->crud->addFilter([
            'name'  => 'category_id',
            'type'  => 'select2',
            'label' => 'Category'
        ], function() {
            return Category::orderBy('description')->pluck('description', 'id')->toArray();
        }, function($value) { // if the filter is active
            $this->crud->addClause('whereHas','timeslot.category', function($query) use ($value){
                $query->where('category_id', $value);
            });
        });

        $this->crud->addFilter([
            'name'  => 'status',
            'type'  => 'select2',
            'label' => 'Status'
        ], function(){
            return EventStatus::pluck('name', 'id')->toArray();
        }, function($value) { // if the filter is active
            $this->crud->addClause('where','status_id', $value);
        });
        $this->crud->denyAccess(['show']);

        $this->crud->set('help',
            'This section list all hearings, and their status, for a specific calender. <br>
                To add an event, use the Court\'s Calendar tool.'
        );
    }

    /**
     * Display all rows in the database for this entity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);


        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getListView(), $this->data);
    }

    /**
     * @param $event
     * @return void
     */
    public function checkIfEmailConfirmationsAreEnabled($event): void
    {
        if ($event->timeslot->court->email_confirmations) {

            $emailFind = self::array_key_exists_wildcard($event->templates_data, '*_|EMAIL', 'key-value');
            $toEmails = [];
            if (isset($event->attorney->email) && $event->attorney->email->isNotEmpty()) {
                foreach ($event->attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }
            if (isset($event->opp_attorney->email) && $event->opp_attorney->email->isNotEmpty()) {
                foreach ($event->opp_attorney->email as $email){
                    $toEmails[] = $email->email;
                }
            }

            if (!empty($event->plaintiff_email)) {
                foreach (explode(';', $event->plaintiff_email) as $email) {
                    $toEmails[] = $email;
                }
            }
            if (!empty($event->defendant_email)) {
                foreach (explode(';', $event->defendant_email) as $email) {
                    $toEmails[] = $email;
                }
            }
            foreach ($event->timeslot->court->judge->ja as $ja) {
                $toEmails[] = $ja->email;
            }

            if (!empty($emailFind) && count($emailFind) > 0) {
                $toEmails = array_merge($emailFind, $toEmails);
            }

            foreach (array_unique($toEmails) as $email) {
                Mail::to($email)->send(new HearingCancellation($event));
            }

        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // echo "=======".$this->crud->getCurrentEntry();
//        $court = $this->crud->getCurrentEntry()->timeslot->court;
//        $permission = CourtPermission::where('user_id',backpack_user()->id)
//        ->where('court_id', $court->id)
//        ->where('active',1)->first();

        $permission = CourtPermission::where('user_id',backpack_user()->id)->where('active',1)->first();

        if (backpack_user()->hasrole('JA') && $permission != null && $permission->editiable) {
            $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'end');
        } else if(backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'end');
        }

        // $this->crud->allowAccess(['revise']);

        $this->crud->setDefaultPageLength(50);
        $this->crud->setPageLengthMenu([50, 100, 200, 300]);
        $this->crud->addButton('line', 'revise', 'view', 'revise-operation::revise_button', 'end');
        $this->crud->denyAccess(['delete']);
        $this->crud->enableExportButtons();
        CRUD::column('case_num')->label('Case Number');
        CRUD::column('motion_id');
        CRUD::addcolumn([
            'name' => 'timeslot',
            'label' => 'Timeslot',
            'type' => 'relationship',
            'entity' => 'timeslot',
            'model' => 'App\Models\Timeslot',
            'attribute' => 'tabledisplay',
            'orderable' => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->join('timeslot_events', 'events.id','=','timeslot_events.event_id')
                    ->join('timeslots', 'timeslot_events.timeslot_id','=','timeslots.id')
                    ->orderby('start', $columnDirection)
                    ->select('events.*');
            }
        ]);

        CRUD::addcolumn([
            'name' => 'Length',
            'label' => 'Duration',
            'type' => 'select',
            'entity' => 'timeslot',
            'model' => 'App\Models\Timeslot',
            'attribute' => 'Length',
            'orderable' => true
        ]);

        CRUD::addcolumn([
            'name' => 'court',
            'label' => 'Court',
            'type' => 'relationship',
            'entity' => 'timeslot',
            'model' => 'App\Models\Timeslot',
            'attribute' => 'courttable'
        ]);
        CRUD::addcolumn([
            'name' => 'status_id',
            'label' => 'Status',
            'type' => 'relationship',
            'entity' => 'status',
            'model' => 'App\Models\EventStatus',
            'attribute' => 'name'
        ]);
        CRUD::column('attorney_id')->searchLogic(
            function ($query, $column, $searchTerm) {
            $query->orwherehas('attorney', function ($q) use ($column, $searchTerm) {
                $q->where('attorneys.bar_num', 'like', '%'.$searchTerm.'%')
                    ->orwhere('attorneys.name', 'like', '%'.$searchTerm.'%');
            });});
        CRUD::column('opp_attorney_id')->label('Opposing Attorney')->searchLogic(
            function ($query, $column, $searchTerm) {
                $query->orwherehas('opp_attorney', function ($q) use ($column, $searchTerm) {
                    $q->where('attorneys.bar_num', 'like', '%'.$searchTerm.'%')
                        ->orwhere('attorneys.name', 'like', '%'.$searchTerm.'%');
                });});
        CRUD::column('plaintiff')->label('Plaintiff');
        CRUD::column('defendant')->label('Defendant');
        CRUD::addcolumn([
            'name' => 'category',
            'label' => 'Category',
            'type' => 'relationship',
            'entity' => 'category',
            'attribute' => 'categorytable',
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->leftJoin('timeslot_events', 'events.id', '=', 'event_id')
                    ->leftJoin('timeslots', 'timeslot_events.timeslot_id', '=', 'timeslots.id')
                    ->leftJoin('categories','timeslots.category_id','=','categories.id')
                    ->orderBy('categories.description', $columnDirection)->select('events.*');
            }
        ]);



        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    	{


        Widget::add()->type('script')->content('assets/js/admin/forms/event.js');

        $court_id = $this->crud->getCurrentEntry()->timeslot->court->id;

        CRUD::setValidation(EventRequest::class);

        CRUD::field('addon')->type('checkbox');

        CRUD::field('case_num')->type('case_number')->wrapper(['class' => 'form-group col-md-12']);

        CRUD::field('motion_id')->type('select2_from_ajax')->attribute('description')
            ->entity('motion')->minimum_input_length(0)
            ->data_source('/api/courtmotions?q=' . $court_id)
            ->wrapper(['class' => 'form-group col-md-6']);

	CRUD::field('custom_motion')
            ->label("Other Motion")
	    ->wrapper(['class' => 'form-group col-md-6 otherMotionShow']);

	CRUD::field('event_type_id')->type('select2_from_ajax')->attribute('name')
            ->entity('type')->minimum_input_length(0)
            ->data_source('/api/courteventtypes?q=' . $court_id)
            ->wrapper(['class' => 'form-group col-md-6']);

        CRUD::field('attorney_id')->type('select2_from_ajax')->data_source('/api/attorney')
            ->wrapper(['class' => 'form-group col-md-6']);

        CRUD::field('opp_attorney_id')->label('Opposing Attorney')->type('select2_from_ajax')->data_source('/api/attorney')
            ->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('plaintiff')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('defendant')->wrapper(['class' => 'form-group col-md-6']);
	 CRUD::field('plaintiff_email')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('defendant_email')->wrapper(['class' => 'form-group col-md-6']);

        CRUD::field('user_defined_fields')->type('user_defined_fields');

        CRUD::field('owner_id')->type('hidden')->value(Auth::id());
        CRUD::field('owner_type')->type('hidden')->value('App\Models\User');

        CRUD::field('notes')->type('textarea');

        if($this->crud->getCurrentEntry()->status_id === 1){
            CRUD::field('cancellation_reason')->wrapper(['class' => 'form-group col-md-12']);
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
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        Event::find($id)->update([
            'status_id' => 1,
            'cancellation_reason' => $request->value
        ]);

        $event = Event::find($id);

        // Check if Email confirmations are enabled
        $this->checkIfEmailConfirmationsAreEnabled($event);

        // Remove all event reminders
        EventReminder::where('event_id', $event->id)->delete();

        // Detach the event from the Timeslot freeing up a slot
        TimeslotEvent::where('event_id', $id)->delete();

        return $event;
    }

    /**
     * Update the specified resource in the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {

	$request = $this->crud->validateRequest();
    $this->validate($request, [
        'otherMotion' => 'max:255',
        'plaintiff' => 'max:255',
        'defendant' => 'max:255',
    ]);
         $event = Event::find($request->id);


        if($event->created_at->format("Y-m-d") <= "2023-05-18") {

            $vaidateCaseNumber = $this->validateCaseNumber($request->case_num,$request->case_num);
        }
        else{
            $format = Court::select('case_num_format')->where('id',$event->timeslot->court->id)->first();
            //dd($event->timeslot->court->id);
            $vaidateCaseNumber = $this->validateCaseNumber($request->case_num,$format->case_num_format);
        }

        if($vaidateCaseNumber != NULL)
        {
           return response()->json(['errors' => ["case_error" => [$vaidateCaseNumber]]], 422);
        }

	$this->crud->hasAccessOrFail('update');

        // dd($request->otherMotion);
        $request->request->add(['template' => json_encode($request->templates_data)]);
        $request->request->add(['custom_motion' => $request->otherMotion ?? '']);
        $request->request->add(['owner_id' => Auth::user()->id, 'owner_type' => 'App\Models\User']);
        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();
        $request->request->add(['template' => json_encode($request->templates_data)]);

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $request->all()
        );

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction(Event::find($request->id));
    }
    function array_key_exists_wildcard ( $array, $search, $return = '' ) {
        if($array != null ) {
            $search = str_replace('\*', '.*?', preg_quote($search, '/'));
            $result = preg_grep('/^' . $search . '$/i', array_keys($array));
            if ($return == 'key-value')
                return array_intersect_key($array, array_flip($result));
            return $result;
        }
    }


    public function bulkDeleteEvent(Request $request)
    {
        $this->crud->hasAccessOrFail('delete');
        $event = Event::whereIn('id', explode(',',$request->updatelist))->get();

        foreach ($event as $evnt){

            // Check if Email confirmations are enabled
            $this->checkIfEmailConfirmationsAreEnabled($evnt);

            $evnt->update([
                'status_id' => 1,
                'cancellation_reason' => $request->cancellation_reason
            ]);

            // Remove all event reminders
           EventReminder::where('event_id', $evnt->id)->delete();

            // Detach the event from the Timeslot freeing up a slot
           TimeslotEvent::where('event_id',$evnt->id)->delete();
        }

        return $event;
    }

    public function caseNumSearch(Request $request)
    {
       // $event = Event::with(['attorney','opp_attorney'])->where('case_num',$request->case_number)->orderBy('id',"DESC")->first();

	   $event = Event::with(['attorney', 'opp_attorney', 'timeslot.court','status', 'motion', 'timeslot.category'])
            ->where('case_num', 'LIKE', '%' . $request->case_number . '%')
            ->orderBy('id', 'desc')
            ->first();

        return $event;
    }
	public function validateCaseNumber($caseNumber, $caseFormat)
    {
        $case_number_error = null;
        if($caseFormat != null)
        {
            $caseFormatArray = explode("-",$caseFormat);
            $caseNumberArray = explode("-",$caseNumber);
            if(count($caseFormatArray) == 1)
            {
                $case_number_error = (strlen($caseNumber) == 0) ?  "Please provide Full UCN!" : null;
            }
            else if(count($caseFormatArray) == 2)
            {
                if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                {
                    $case_number_error = "Please provide complete year!";
                }
                else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 ||  strlen($caseNumberArray[1]) > 8)
                {
                    $case_number_error = "Please provide valid case number!";
                }
            }
            else if(count($caseFormatArray) == 3)
            {
                if(!isset($caseNumberArray[0]) ||!is_numeric($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                {
                    $case_number_error = "Please provide complete year!";
                }
                else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 )
                {
                    $case_number_error = "Please select Case Code!";
                }
                else if(!isset($caseNumberArray[2]) ||!is_numeric($caseNumberArray[2])|| strlen($caseNumberArray[2]) < 3 ||  strlen($caseNumberArray[2]) > 7)
                {
                    $case_number_error = "Please provide valid case number!";
                }
            }
            else if(count($caseFormatArray) == 3)
            {
                if(strlen($caseFormatArray[1]) == 2 || $caseFormatArray[1]==0 ){
                    if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                    {
                        $case_number_error = "Please provide complete year!";
                    }
                    else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 )
                    {
                        $case_number_error = "Please select Case Code!";
                    }
                    else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 ||  strlen($caseNumberArray[2]) > 7)
                    {
                        $case_number_error = "Please Enter case number!";
                    }
                }
                else{
                    if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) != 4 ||  strlen($caseNumberArray[0]) > 4)
                    {
                        $case_number_error = "Please provide complete year!";
                    }
                    else if(!isset($caseNumberArray[1]) || strlen($caseNumberArray[1]) < 1 || strlen($caseNumberArray[2]) > 7)
                    {
                        $case_number_error = "Please Enter case number!";
                    }
                    else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 ||  strlen($caseNumberArray[2]) > 4)
                    {
                        $case_number_error = "Please provide Party/Defendant Identifier!";
                    }
                }
            }
            else if(count($caseFormatArray) == 6 || count($caseFormatArray) == 5 || count($caseFormatArray) == 4){
                if(!isset($caseNumberArray[0]) || strlen($caseNumberArray[0]) < 1 ||  strlen($caseNumberArray[0]) > 2)
                {
                    $case_number_error = "Please provide valid county number!";
                }
                else if(!isset($caseNumberArray[1]) || !is_numeric($caseNumberArray[1]) || strlen($caseNumberArray[1]) != 4 ||  strlen($caseNumberArray[1]) > 4)
                {
                    $case_number_error = "Please provide complete year with Numeric only!";
                }
                else if(!isset($caseNumberArray[2]) || strlen($caseNumberArray[2]) < 1 )
                {
                    $case_number_error = "Please select Case Code!";
                }
                else if(!isset($caseNumberArray[3]) ||!is_numeric($caseNumberArray[3]) || strlen($caseNumberArray[3]) < 3 || strlen($caseNumberArray[3]) > 6 )
                {
                    $case_number_error = "Please Enter case number!";
                }
                else if(!isset($caseNumberArray[4]) || strlen($caseNumberArray[4]) < 1 ||  strlen($caseNumberArray[4]) > 4)
                {
                    $case_number_error = "Please provide Party/Defendant Identifier!";
                }
                else if(!isset($caseNumberArray[5]) || strlen($caseNumberArray[5]) < 1 ||  strlen($caseNumberArray[5]) > 2)
                {
                    $case_number_error = "Please provide Branch Location";
                }
            }

        }
        else{
            $case_number_error = (strlen($caseNumber) == 0) ?  "Please Enter case number!" : null;
        }
        return $case_number_error;
    }
}

