<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TemplateRequest;
use App\Models\Category;
use App\Models\Court;
use App\Models\CourtTemplateOrder;
use App\Models\CourtType;
use App\Models\Template;
use App\Models\TemplateTimeslot;
use App\Models\TimeslotMotion;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\CourtPermission;
use Backpack\ReviseOperation\ReviseOperation;

/**
 * Class TemplateCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TemplateCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    use ReviseOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Template::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/template');
        CRUD::setEntityNameStrings('template', 'templates');
        $this->crud->denyAccess(['show']);

        $this->crud->set('help',
            'This section allows a user to define predefined templates for calendar timeslot automation.'
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
	     $this->crud->setDefaultPageLength(50);
        $this->crud->setPageLengthMenu([50, 100, 200, 300]);
        if(backpack_user()->hasRole('JA')){
            $values = backpack_user()->courtsFilter();

            $this->crud->addClause('whereHas','court', function($query) use ($values){
                $query->whereIn('court_id', backpack_user()->courts());
            });
        }

        $permission = CourtPermission::where('user_id',backpack_user()->id)->where('active',1)->first();

        if (backpack_user()->hasrole('JA') && $permission->editable) {
            $this->crud->allowAccess(['update']);
            $this->crud->allowAccess(['delete']);
            $this->crud->allowAccess(['clone']);
            //$this->crud->allowAccess(['template_view']);
            $this->crud->addButtonFromView('line', 'template_view', 'template', 'end');
        } else if(!backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->denyAccess(['update']);
            $this->crud->denyAccess(['delete']);
            $this->crud->denyAccess(['clone']);
            $this->crud->denyAccess(['template_view']);
        }
        else if(backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->addButtonFromView('line', 'template_view', 'template', 'end');
        }


        CRUD::column('name');

        CRUD::addColumn([
            'name' => 'judge',
            'label' => 'Judge',
            'type' => 'model_function',
            'function_name' => 'getJudge',
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query
                    ->join('judges', 'court_templates.court_id', '=','judges.court_id')
                    ->orderBy('judges.name', $columnDirection)->select('court_templates.*');
	    },
	    'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('court.judge', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            },
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
        CRUD::setValidation(TemplateRequest::class);

        CRUD::field('name')->wrapper(['class' => 'form-group col-md-6']);

        if(backpack_user()->hasRole(['JA'])) {
            CRUD::field('court_id')->type('relationship')->entity('court')->attribute('judge.name')->options(function ($query) {
                return $query->has('judge')->whereIn('id', backpack_user()->courts())->get()->sortBy(['judge.name','asc']);
            })->wrapper(['class' => 'form-group col-md-6']);
        }
        else{
            CRUD::field('court_id')->type('relationship')->entity('court')->attribute('judge.name')->options(function ($query) {
                return $query->has('judge')->get()->sortBy(['judge.name','asc']);
            })->wrapper(['class' => 'form-group col-md-6']);
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
    }

    protected function configure(Template $template){

        $format = Court::select('case_num_format')->where('id',$template->court->id)->first();
        $court_types = CourtType::select('old_id')->get();

        return view('admin.template',
            [
                'template' => $template,
                'court' => $template->court,
                'categories' => Category::all()->sortBy('description'),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $template = $this->crud->getCurrentEntry();

        if($template->timeslots != null){
            foreach($template->timeslots as $timeslot){

                if($timeslot->motions != null){
                    foreach($timeslot->motions as $motion){
                        $motion->delete();
                    }
                }

                $timeslot->delete();
            }
        }

        $manual_orders = CourtTemplateOrder::where('template_id', $template->id)->get();

        if($manual_orders != null){
            foreach($manual_orders as $order){
                $order->delete();
            }
        }

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }

    public function bulkDelete()
    {
        $this->crud->hasAccessOrFail('bulkDelete');

        $entries = request()->input('entries', []);
        $deletedEntries = [];

        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {

                $template = $entry;

                if($template->timeslots != null){
                    foreach($template->timeslots as $timeslot){

                        if($timeslot->motions != null){
                            foreach($timeslot->motions as $motion){
                                $motion->delete();
                            }
                        }

                        $timeslot->delete();
                    }
                }

                $manual_orders = CourtTemplateOrder::where('template_id', $template->id)->get();

                if($manual_orders != null){
                    foreach($manual_orders as $order){
                        $order->delete();
                    }
                }

                $deletedEntries[] = $entry->delete();
            }
        }

        return $deletedEntries;
    }

    public function clone($id)
    {
        $this->crud->hasAccessOrFail('clone');
        $this->crud->setOperation('clone');

        $old_template = Template::find($id);

        $new_template = Template::create([
            'name' => $old_template->name . ' (Copy)',
            'court_id' => $old_template->court_id
        ]);

        foreach($old_template->timeslots as $timeslot){
            $new_timeslot = TemplateTimeslot::create([
                'start' => $timeslot->start,
                'end' => $timeslot->end,
                'duration' => $timeslot->duration,
                'quantity' => $timeslot->quantity,
                'allDay' => $timeslot->allDay,
                'day' => $timeslot->day,
                'court_template_id' => $new_template->id,
                'description' => $timeslot->description,
                'category_id' => $timeslot->category_id,
                'blocked' => $timeslot->blocked,
                'public_block' => $timeslot->public_blocked == 'on',
                'block_reason' => $timeslot->block_reason,
            ]);


            foreach($timeslot->motions as $motion){
                TimeslotMotion::create([
                    'timeslotable_type' => $motion->timeslotable_type,
                    'timeslotable_id' => $new_timeslot->id,
                    'motion_id' => $motion->motion_id
                ]);
            }

        }



    }
}
