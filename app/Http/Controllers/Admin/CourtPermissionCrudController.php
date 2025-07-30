<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CalendarPermissionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Carbon\Carbon;

/**
 * Class CourtPermissionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CourtPermissionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\CourtPermission::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/court-permission');
        CRUD::setEntityNameStrings('permission', 'court permissions');

        if(!backpack_user()->hasRole('System Admin')){
            $this->crud->denyAccess([ 'create','show','delete','revise']);
        }

        $this->crud->denyAccess(['show']);

        $this->crud->set('help',
            'This section list user\'s access to a specific Judge. <br>
                In order for a non System Admin user to see a calendar, they must have permissions to the Judge. <br>
                Non System Admin users can change which calendar\'s are active here.'
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

        if(backpack_user()->hasRole('System Admin')){
            $this->crud->addFilter([
                'name'        => 'user_id',
                'type'        => 'select2_ajax',
                'label'       => 'User',
                'placeholder' => 'Pick a User',
            ],
                url('api/user'), // the ajax route
                function($value) { // if the filter is active
                    $this->crud->addClause('where', 'user_id', $value);
                });

            $this->crud->addFilter([
                'name'        => 'judge_id',
                'type'        => 'select2_ajax',
                'label'       => 'Judge',
                'placeholder' => 'Pick a Judge'
            ],
                url('api/judge'), // the ajax route
                function($value) { // if the filter is active
                    $this->crud->addClause('where', 'judge_id', $value);
                });
        }


        if(backpack_user()->hasRole('JA')){
            $this->crud->addClause('where', 'user_id', '=', backpack_user()->id);
        }


        CRUD::addColumn([
            'name' => 'user_id',
            'label' => 'User',
            'type' => 'relationship',
            'entity' => 'ja',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('ja', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            },
'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->join('users', 'court_permissions.user_id', '=','users.id')
                    ->orderBy('name', $columnDirection)->select('court_permissions.*');
                }
        ]);

        CRUD::addColumn([
            'name' => 'judge_id',
            'label' => 'Judge',
            'type' => 'relationship',
            'entity' => 'judges',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('judges', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            },
 'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->join('judges', 'court_permissions.judge_id', '=','judges.id')
                    ->orderBy('name', $columnDirection)->select('court_permissions.*');
                }
        ]);

        CRUD::addColumn([
            'name' => 'active',
            'label' => 'Active',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'editable',
            'label' => 'Permissions',
            'type' => 'boolean',
            'options' => [ true => 'View and Edit', false => 'View Only']
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
        CRUD::setValidation(CalendarPermissionRequest::class);


        CRUD::addField([
            'name' => 'active',
            'label' => 'Active',
            'type' => 'radio',
            'options' => [true => 'Yes', false => 'No'],
            'inline' => true,
            'wrapper' => ['class' => 'form-group col-md-6']
        ]);

        if(backpack_user()->hasRole('System Admin')){
            CRUD::addField([
                'name' => 'editable',
                'label' => 'Calendar Permissions',
                'type' => 'select_from_array',
                'wrapper' => ['class' => 'form-group col-md-6 '],
                'options' => [true => 'View and Edit', false => 'View Only'],
            ]);

            CRUD::addField([
                'name' => 'user_id',
                'label' => 'User',
                'type' => 'relationship',
                'entity' => 'ja',
                'attribute' => 'name',
                'model' => 'App\Models\User',
                'wrapper' => ['class' => 'form-group col-md-6'],
                'options' => (function ($query) {
                    return $query->orderBy('name', 'asc')->get();
                })
            ]);

            CRUD::addField([
                'name' => 'judge_id',
                'label' => 'Judge',
                'type' => 'relationship',
                'entity' => 'judges',
                'attribute' => 'name',
                'model' => 'App\Models\Judge',
                'wrapper' => ['class' => 'form-group col-md-6'],
                'options' => (function ($query) {
                    return $query->orderBy('name', 'asc')->get();
                })

            ]);
        }



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
	    return $this->customePerformSaveAction();
    }
    public function customePerformSaveAction()
    {
        return \Redirect::to(route('court-permission.index'));
    }
}
