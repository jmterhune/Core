<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\JudgeRequest;
use App\Models\Court;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;

/**
 * Class JudgeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class JudgeCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Judge::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/judge');
        CRUD::setEntityNameStrings('judge', 'judges');

        // Default Order by
        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('name','asc');
        }

        // Authorization
        if(!backpack_user()->hasRole('System Admin')){
            $this->crud->denyAccess([ 'create','list','show','update','delete','revise']);
        }
        $this->crud->denyAccess('show');

        $this->crud->set('help',
            'This section list all Judges and their assigned court. <br> Judges can be assigned to a court here.'
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
        CRUD::column('title');
        CRUD::column('name');
        CRUD::addColumn([
            'name' => 'court_id',
            'type' => 'relationship',
            'label' => 'Assigned Court',
            'entity' => 'court',
            'attribute' => 'description',
            'model' => 'App\Models\Court',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('court', function ($q) use ($column, $searchTerm) {
                    $q->where('description', 'like', '%'.$searchTerm.'%');
                });
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
        CRUD::setValidation(JudgeRequest::class);

        CRUD::addfield([
            'name' => 'title',
            'type' => 'select_from_array',
            'options' => [
                'Judge' => 'Judge', 'Mediator' => 'Mediator', 'Magistrate' => 'Magistrate' , 'Case Manager' => 'Case Manager',
                'Hearing Officer' => 'Hearing Officer'
            ],
            'wrapper' => [
                'class' => 'form-group col-md-3'
            ]
        ]);
        CRUD::addfield([
            'name' => 'name',
            'wrapper' => [
                'class' => 'form-group col-md-5'
            ]

        ]);
        CRUD::addfield([
            'name' => 'phone',
            'type' => 'number',
            'wrapper' => [
                'class' => 'form-group col-md-4'
            ]
        ]);
        CRUD::addField([
            'name' => 'court_id',
            'type' => 'relationship',
            'entity' => 'court',
            'attribute' => 'description',
            'model' => 'App\Models\Court',
            'options' => (function ($query) {
                return $query->orderBy('description', 'asc')->get();
            })
        ]);


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
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // If court is removed from a Judge, disable web scheduling for the court.
        if($request->court_id == null){
            $court = Court::find($this->crud->getCurrentEntry()->court_id);
            if($court != null && $court->scheduling){
                $court->scheduling = false;
                $court->save();
            }
        }

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
