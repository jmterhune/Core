<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MediationInstructionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MediationInstructionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MediationInstructionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\MediationInstruction::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . 'mediation/instructions');
        CRUD::setEntityNameStrings('instruction', 'instructions');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('county_id');
        CRUD::column('location_type_id')->entity('event')->type('select')->model('App\Models\EventType');
        CRUD::column('case_type');

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
        CRUD::setValidation(MediationInstructionRequest::class);

        CRUD::field('county_id')->type('select')
            ->entity('county')->attribute('name')
            ->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('location_type_id')->type('select')
            ->entity('event')->attribute('name')->name('location_type_id')
            ->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('case_type')
            ->type('select_from_array')->options(['family' => 'Family', 'civil' => 'Civil'])
            ->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('instruction')->label('Instructions')->type('easymde');


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
}
