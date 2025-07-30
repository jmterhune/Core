<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MotionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Carbon\Carbon;

/**
 * Class MotionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MotionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Motion::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/motion');
        CRUD::setEntityNameStrings('motion', 'motions');

        // Default Order By
        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('description','asc');
        }

        // Authorization
        if(!backpack_user()->hasRole('System Admin')){
            $this->crud->denyAccess([ 'create','list','show','update','delete','revise']);
        }

        $this->crud->denyAccess('show');

        $this->crud->set('help',
            'This section list all available motions that can be used for hearings.'
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
        CRUD::column('description');
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
        CRUD::setValidation(MotionRequest::class);


        CRUD::field('description');
        CRUD::addfield([
            'name' => 'lag',
            'wrapper' => [ 'class' => 'form-group col-md-6']
        ]);
        CRUD::addfield([
            'name' => 'lead',
            'wrapper' => [ 'class' => 'form-group col-md-6']
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

    public function getCourtTimeSlotMotions(Request $request)
    {
        $start_time = Carbon::create($request->start);
        $end_time = Carbon::create($request->end);
        return \DB::table("motions")->select("motions.id", "motions.description")
                                                ->join('timeslot_motions', "timeslot_motions.motion_id","motions.id")
                                                ->join('court_timeslots', "court_timeslots.timeslot_id","timeslot_motions.timeslotable_id")
                                                ->join('timeslots', "timeslots.id","timeslot_motions.timeslotable_id")
                                                ->where("court_timeslots.court_id",$request->court_id)
                                                ->where("start",">=",$start_time)
                                                ->where("end","<=",$end_time)
                                                ->groupBy("motions.id")
                                                ->groupBy("motions.description")
                                                ->get();
    }
}
