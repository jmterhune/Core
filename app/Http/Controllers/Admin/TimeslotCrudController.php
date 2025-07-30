<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TimeslotRequest;
use App\Models\CourtPermission;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



/**
 * Class TimeslotCrudControllerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TimeslotCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Timeslot::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/timeslot-crud');
        CRUD::setEntityNameStrings('timeslot', 'timeslot');
        $this->crud->denyAccess(['show','create','update','delete']);

        $this->crud->set('help',
            'This section allows a user to see all timeslots for a particular calender.
                <br> By utilizing the filters a user can find timeslots that are available.'
        );
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        $permission = CourtPermission::where('user_id',backpack_user()->id)->where('active',1)->first();

        if (backpack_user()->hasrole('System Admin') || (backpack_user()->hasrole('JA') && $permission->editable)) {

            $this->crud->addButtonFromModelFunction('line', 'court', 'getCreateEventURL', 'end');
        }

        $this->crud->setDefaultPageLength(50);
        $this->crud->setPageLengthMenu([10, 25, 50, 100]);

        $this->crud->addClause('where', 'blocked', '=', false);
        $this->crud->addClause('where', 'public_block', '=', false);
        $this->crud->addClause('where', 'quantity', '>', DB::raw('(select count(*) as count from timeslot_events where timeslot_events.timeslot_id = timeslots.id and timeslot_events.deleted_at is null)'));
        $this->crud->addClause('wherehas', 'court', function($query){
            $query->wherein('courts.id',Auth::user()->courts());
        });

        $this->crud->addFilter([
            'name'  => 'court',
            'type'  => 'dropdown',
            'label' => 'Court'
        ], backpack_user()->courtsFilter(), function($value) { // if the filter is active
            $this->crud->addClause('whereHas','court', function($query) use ($value){
                $query->where('court_id', $value);
            });
        });

        $this->crud->addFilter([
            'type'  => 'date_range',
            'name'  => 'from_to',
            'label' => 'Date range'
        ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'start', '>=', $dates->from);
                $this->crud->addClause('where', 'end', '<=', $dates->to . ' 23:59:59');
            });

        if (!$this->crud->getRequest()->has('from_to')) {
            $this->crud->addClause('where', 'start', '>=', Carbon::now()->startOfDay());
        }

        if(backpack_user()->hasRole('JA')){
            $values = backpack_user()->courtsFilter();

            $this->crud->addClause('whereHas','court', function($query) use ($values){
                $query->whereIn('court_id', backpack_user()->courts());
            });
        }

        CRUD::addcolumn([
            'name' => 'court',
            'label' => 'Court',
            'type' => 'relationship',
            'entity' => 'court',
            'model' => 'App\Models\Court',
	    'attribute' => 'description',
	    'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('court', function ($q) use ($column, $searchTerm) {
                    $q->where('description', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        CRUD::addColumn([
            'name' => 'TableDisplay',
            'label' => 'Date/Time',
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('start', $columnDirection);
            }
        ]);
        CRUD::addColumn([
            'name' => 'Length',
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('duration', $columnDirection);
            }
        ]);
        CRUD::addColumn([
            'name' => 'Available',
            'type' => 'boolean',
        ]);
        CRUD::addColumn([
            'name' => 'quantity'
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
        CRUD::setValidation(TimeslotRequest::class);

        //CRUD::setFromDb(); // fields

        CRUD::addField([
            'name' => 'start',
            'type' => 'datetime',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'end',
            'type' => 'datetime',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'duration',
            'type' => 'select_from_array',
            'options' => [
                '5' => '5 mins',
                '10' => '10 mins',
                '15' => '15 mins',
                '20' => '20 mins',
                '30' => '30 mins',
                '45' => '45 mins',
                '60' => '1 hour',
                '90' => '1.5 hour',
                '120' => '2 hours',
                '150' => '2.5 hours',
                '165' => '2.75 hours',
                '180' => '3 hours',
                '210' => '3.5 hours',
                '240' => '4 hours',
                '300' => '5 hours',
                '360' => '6 hours',
                '480' => '8 hours',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'quantity',
            'type' => 'number',
            'label' => 'Quantity',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'description',
            'type' => 'text',
            'label' => 'Description',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([

            'label' => 'Category',
            'type' => 'select',
            'name' => 'category',
            'entity' => 'category',
            'model' => 'App\Models\Category',
            'attribute' => 'description',
            'options'   => (function ($query) {
                return $query->orderBy('description', 'asc')->get();
            }),
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'court_id',
            'label' => 'Court',
            'type' => 'hidden',
            'value' => $this->crud->getCurrentEntry()->court->id
        ]);

//        CRUD::addField([
//            'name' => 'eventstest',
//            'label' => 'Hearings',
//            'type' => "relationship_custom",
//            // ..
//            'subfields'   => [
//                [
//                    'name' => 'case_num',
//                    'label' => 'Case Number',
//                    'type' => 'text',
//                    'attributes' => [
//                        'readonly' => 'readonly'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-12',
//                    ],
//                ],
//                [
//                    'name' => 'motion_id',
//                    'type' => 'relationship',
//                    'entity' => 'motion',
//                    'attribute' => 'description',
//                    'attributes' => [
//                        'disabled' => 'disabled'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//                [
//                    'name' => 'type_id',
//                    'type' => 'relationship',
//                    'entity' => 'type',
//                    'attribute' => 'name',
//                    'attributes' => [
//                        'disabled' => 'disabled'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//                [
//                    'name' => 'attorney_id',
//                    'type' => 'relationship',
//                    'entity' => 'attorney',
//                    'attribute' => 'name',
//                    'attributes' => [
//                        'disabled' => 'disabled'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//                [
//                    'name' => 'opp_attorney_id',
//                    'label' => 'Opposing Attorney',
//                    'type' => 'relationship',
//                    'entity' => 'opp_attorney',
//                    'attribute' => 'name',
//                    'attributes' => [
//                        'disabled' => 'disabled'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//                [
//                    'name' => 'plaintiff',
//                    'type' => 'text',
//                    'attributes' => [
//                        'readonly' => 'readonly'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//                [
//                    'name' => 'defendant',
//                    'type' => 'text',
//                    'attributes' => [
//                        'readonly' => 'readonly'
//                    ],
//                    'wrapper' => [
//                        'class' => 'form-group col-md-6',
//                    ],
//                ],
//            ],
//        ]);


        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
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
     * Update the specified resource in the database.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function update()
    {

       // $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Remove court_id as it does not exist with this context via request from the frontend calendar view
        $request->request->remove('court_id');

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest($request)
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }




}
