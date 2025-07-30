<?php

namespace App\Http\Controllers\Admin;
use App\Models\MediationNotAvailableTimings;
use App\Models\MediationMediator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use App\Http\Requests\MediationNotAvailableScheduleRequest;


/**
 * Class AvailableScheduleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MediationNotAvailableScheduleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\MediationNotAvailableTimings::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/mediation/notavailableschedule');
        CRUD::setEntityNameStrings('Not Available Schedule', 'Not Available Schedule');

        CRUD::setValidation(MediationNotAvailableScheduleRequest::class);

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->denyAccess(['revise']);
        }

        $this->crud->addFilter([
            'name'  => 'mediator',
            'type'  => 'select2',
            'label' => 'Mediator'
        ], function(){
            return MediationMediator::pluck('name', 'id')->toArray();
        }, function($value) { // if the filter is active
            $this->crud->addClause('where','Dd_med', $value);
        });

        
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
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // $this->crud->setDefaultPageLength(50);
        // $this->crud->setPageLengthMenu([50, 100, 200, 300]);
        // $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'end');
        // $this->crud->addButton('line', 'revise', 'view', 'revise-operation::revise_button', 'end');
        // $this->crud->denyAccess(['delete']);
        // $this->crud->enableExportButtons();

        // CRUD::addcolumn([
        //     'name' => 'Mediator',
        //     'label' => 'Mediator',
        //     'type' => 'relationship',
        //     'entity' => 'medmaster',
        //     'model' => 'App\Models\MediationMediator',
        //     'attribute' => 'name'
        // ]);
        CRUD::addcolumn([
            'name' => 'Mediator',
            'label' => 'Mediator',
            'type' => 'relationship',
            'entity' => 'medmaster',
            'model' => 'App\Models\MediationMediator',
            'attribute' => 'name',
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->join('mediation_mediators', 'mediation_not_avail_times.Dd_med', '=','mediation_mediators.id')
                    ->orderBy('mediation_mediators.name', $columnDirection);
            }
        ]);
        $this->crud->set('reorder.label', 'Mediator');
        CRUD::column('Tb_sdate')->label('Begin')->type('closure')->function(function($entry) {
            return date('Y-m-d',strtotime($entry->Tb_sdate));
        });
        CRUD::column('Tb_edate')->label('End')->type('closure')->function(function($entry) {
            return date('Y-m-d',strtotime($entry->Tb_edate));
        });
        CRUD::column('Dd_time')->label('Time');
        CRUD::column('at_weekday')->label('Week Day')->type('closure')->function(function($entry) {
            $week = [1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 4 => "Thursday", 5 => "Friday"];
            return $week[$entry->at_weekday];
        });
        
        CRUD::set('reorder.label', 'name');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::field('Dd_med')->label("Mediator")->type('select2')->attribute('Mediator')
        ->entity('medmaster')->minimum_input_length(0)
        //->data_source('/api/medmaster')
        ->model("App\Models\MediationMediator")
        ->attribute('name')
        ->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('Tb_sdate')->type('date')->label("Start Date")->wrapper(['class' => ' form-group col-md-4']);

        CRUD::field('Tb_edate')->type('date')->label("End Date")->wrapper(['class' => ' form-group col-md-4']);
        
        CRUD::field('Dd_time')->inline(true)->label('Timeslot')->type('select2_from_array')->options([
            '09:30' => '9:30 am',
            '12:30' => '12:30 pm',
            '13:30' => '1:30 pm',
            '16:30' => '4:30 pm'
        ])->wrapper(['class' => 'form-group col-md-4'])->allows_multiple(($this->crud->getCurrentOperation() === "update") ? false : true);
        CRUD::field('at_weekday')->inline(true)->label('Week Day')->type('select2_from_array')
        ->options([1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 4 => "Thursday", 5 => "Friday"])
        ->wrapper(['class' => 'form-group col-md-2'])->allows_multiple(($this->crud->getCurrentOperation() === "update") ? false : true);

       CRUD::replaceSaveActions(
            [
                'name' => 'save',
                'visible' => function($crud) {
                    return true;
                },
                'redirect' => function($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );

    }

    public function store(Request $request)
    {
        $request = $this->crud->validateRequest();
        foreach($request->input('Dd_time') as $Dd_time)
        {
            foreach($request->input('at_weekday') as $at_weekday)
            {
                $notavlSchedule = new MediationNotAvailableTimings;
                $notavlSchedule->Dd_med = $request->input('Dd_med');
                $notavlSchedule->Tb_sdate = $request->input('Tb_sdate');
                $notavlSchedule->Tb_edate = $request->input('Tb_edate');
                $notavlSchedule->Dd_time = $Dd_time;
                $notavlSchedule->at_weekday = $at_weekday;
                $notavlSchedule->save();
            }
        }

        Alert::success(trans('backpack::crud.insert_success'))->flash();
        return Redirect::to("mediation/notavailableschedule");
        
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

    public function update()
    {
        $request = $this->crud->validateRequest();
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $request->all()
        );

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction(MediationNotAvailableTimings::find($request->id));
    }

}

