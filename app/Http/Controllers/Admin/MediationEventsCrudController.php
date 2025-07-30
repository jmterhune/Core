<?php

namespace App\Http\Controllers\Admin;
use App\Models\MediationEvents;
use App\Models\CourtPermission;
use App\Models\MediationMediator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Prologue\Alerts\Facades\Alert;

use App\Http\Requests\MediationMediatorRequest;


/**
 * Class MediationEventsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MediationEventsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use ReviseOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\MediationEvents::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/mediation/events');
        CRUD::setEntityNameStrings('Mediation Events', 'Mediation Events');

        // CRUD::setValidation(MediationMediatorRequest::class);

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin'])){
            // $this->crud->denyAccess(['revise']);
        }

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

        $permission = CourtPermission::where('user_id',backpack_user()->id)->where('active',1)->first();

        if (backpack_user()->hasrole('System Admin') || (backpack_user()->hasrole('JA') && $permission->editable)) {

            $this->crud->addButtonFromModelFunction('line', 'event', 'getEditEventURL', 'end');
        }
        CRUD::addcolumn([
            'name' => 'Case Number',
            'label' => 'Case Number',
            'type' => 'relationship',
            'entity' => 'case',
            'model' => 'App\Models\MediationCases',
            'attribute' => 'c_caseno',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('case', function ($q) use ($column, $searchTerm) {
                    $q->where('c_caseno', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);

        CRUD::addcolumn([
            'name' => 'Mediator',
            'label' => 'Mediator',
            'type' => 'relationship',
            'entity' => 'medmaster',
            'model' => 'App\Models\MediationMediator',
            'attribute' => 'name'
        ]);


        CRUD::column('ESchDatetimeAmpm')->label('Scheduled Date Time');
        CRUD::column('e_sch_length')->label('Length');
        //CRUD::column('e_med_fee')->label('Mediation Fee');
        CRUD::column('e_pltf_chg')->label('Plaintiff Fee');
        CRUD::column('e_def_chg')->label('Defendant Fee');

        CRUD::addcolumn([
            'name' => 'Outcome',
            'label' => 'Outcome',
            'type' => 'relationship',
            'entity' => 'outcome',
            'model' => 'App\Models\MediationOutcome',
            'attribute' => 'o_outcome'
        ]);

        $this->crud->addFilter([
            'name'  => 'mediator',
            'type'  => 'select2',
            'label' => 'Mediator'
        ], function(){
            return MediationMediator::pluck('name', 'id')->toArray();
        }, function($value) { // if the filter is active
            $this->crud->addClause('where','e_m_id', $value);
        });

        $this->crud->addFilter([
            'label' => 'Scheduled Date Range',
            'type'  => 'date_range',
            'name'  => 'e_sch_datetime',
        ],
        false,
        function ($value) {
            $dates = json_decode($value);
            $this->crud->addClause('where', 'e_sch_datetime', '>=', $dates->from);
            $this->crud->addClause('where', 'e_sch_datetime', '<=', $dates->to );
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {

        CRUD::setValidation(MediationMediatorRequest::class);

        CRUD::field('name')->type('text')->label("Name")->wrapper(['class' => ' form-group col-md-6']);
        CRUD::field('email')->type('email')->label("Email")->wrapper(['class' => ' form-group col-md-6']);
        // CRUD::field('type')->type('text')->label("Type")->wrapper(['class' => ' form-group col-md-6']);
        CRUD::field('phone')->type('text')->label("Phone")->wrapper(['class' => ' form-group col-md-6']);
        CRUD::field('address')->type('textarea')->label("Address")->wrapper(['class' => ' form-group col-md-6']);
        //CRUD::field('csz')->type('text')->label("CSZ")->wrapper(['class' => ' form-group col-md-6']);

        CRUD::field('active')->type('checkbox')->label("Active")->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('contract')->type('checkbox')->label("Contract")->wrapper(['class' => 'form-group col-md-6']);



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

        return $this->crud->performSaveAction(MediationMediator::find($request->id));
    }

}

