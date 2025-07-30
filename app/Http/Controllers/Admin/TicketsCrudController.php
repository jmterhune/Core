<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AttorneyRequest;
use App\Mail\NewAttorney;
use App\Mail\AttorneyPasswordReset;
use App\Mail\NewAttorneyPassword;
use App\Mail\NewTicket;
use App\Mail\TicketSloved;
use App\Mail\TicketUpdate;
use App\Mail\TicketSubmited;
use App\Models\Tickets;
use App\Models\TicketStatus;
use App\Models\TicketsPriority;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;


/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TicketsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    // use ReviseOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        // $this->crud->addButtonFromView('line', 'reset', 'reset', 'beginning');
        // $this->crud->addButtonFromView('line', 'reset', 'reset', 'beginning');
        CRUD::setModel(\App\Models\Tickets::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tickets');
        CRUD::setEntityNameStrings('tickets', 'tickets');

        // Default Order by
        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('id','asc');
        }

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin', 'JA'])){
            $this->crud->denyAccess(['create','list','show','update','delete','revise']);
        }

        if(backpack_user()->hasRole([ 'System Admin'])){
            $this->crud->denyAccess(['revise','delete','create']);
        }
        else if(backpack_user()->hasRole([ 'JA'])){
            $this->crud->denyAccess(['revise','delete','update']);
            $this->crud->addClause('where','created_by',backpack_user()->id);
            $this->crud->addClause('where','created_user_type','App\Models\User');
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

        CRUD::addColumn(['name' => 'ticket_number']);
        CRUD::addColumn(['name' => 'subject']);
        CRUD::addcolumn([
            'name' => 'priority_id',
            'label' => 'Priority',
            'type' => 'relationship',
            'entity' => 'priority',
            'model' => 'App\Models\TicketsPriority',
            'attribute' => 'name'
        ]);

        CRUD::addcolumn([
            'name' => 'status_id',
            'label' => 'Status',
            'type' => 'relationship',
            'entity' => 'status',
            'model' => 'App\Models\TicketStatus',
            'attribute' => 'name'
        ]);

        CRUD::addColumn([
            'name'              => 'usertickets.ticketuserrole',
            'label'             => 'Role',
            'type'              => 'text',
            'type' => 'relationship',
        ]);
        CRUD::addcolumn([
            'name' => 'owner',
            'label' => 'Raised By',
            'type' => 'relationship',
            //'entity' => 'user',
            'attribute' => 'name',

        ]);

        CRUD::addColumn([
            'name'              => 'created_at',
            'label'             => 'Date/Time',
            'type'              => 'datetime',
            'format'            => 'DD.MM.Y - H:mm',
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
        $attributes = [];

        if(backpack_user()->hasAnyRole(['System Admin'])){
        CRUD::addField(
                [
                    'name' => 'ticket_number',
                    'type' => 'text',
                    'attributes' => [
                        'readonly' => 'readonly'
                    ],
                    'wrapper' => [
                        'class' => 'form-group col-md-6',
                    ],
                ],

            );

            $attributes = [ 'disabled' => 'disabled'];
        }


        if(backpack_user()->hasAnyRole(['System Admin'])){

            CRUD::addField([
                'name' => 'status_id',
                'label' => 'Status',
                'type' => 'select',
                'entity' => 'status',
                'model' => 'App\Models\TicketStatus',
                'attribute' => 'name',
                'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), //  you can use this to filter the results show in the select
            ]);

            CRUD::addField([
                'name' => 'priority_id',
                'label' => 'Priority',
                'type' => 'select',
                'entity' => 'priority',
                'model' => 'App\Models\TicketsPriority',
                'options'   => (function ($query) {
                    return $query->orderBy('name', 'ASC')->get();
                }), //  you can use this to filter the results show in the select

            ]);

        }

        CRUD::addField(
            [
                'name' => 'subject',
                'attributes' => $attributes,
                'wrapper' => [
                    'class' => 'form-group col-md-12',
                ],
            ],
        );
        CRUD::addField(
            [
                'name' => 'issue',
                'attributes' => $attributes,
                'wrapper' => [
                    'class' => 'form-group col-md-12',
                ],
            ],
        );
        if(!backpack_user()->hasAnyRole(['System Admin'])){
            CRUD::addField([
                'name'      => 'file',
                'label'     => 'File',
                'type'      => 'upload',
                'upload'    => true,
                'disk'      => 'ticket_uploads',
            ]);
        }

        if(backpack_user()->hasAnyRole(['System Admin'])){
            CRUD::addField(
                [
                    'name' => 'comment',
                    'wrapper' => [
                        'class' => 'form-group col-md-12',
                    ],
                ],
            );
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
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {

        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        // echo json_encode($request->all());exit;
        // $request['password'] = $this->crud->getRequest()->bar_num;

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();
        // insert item in the db
        $item = Tickets::create([
            'subject' => $request->subject,
            'issue' => $request->issue,
            //'priority_id' => $request->priority_id,
            'ticket_number' => "T-".(Tickets::max('id')+1000),
            'created_by' => backpack_user()->id,
            'file' => backpack_user()->id,
            'created_user_type' => 'App\Models\User'
        ]);
        // $this->data['entry'] = $this->crud->entry = $item;
        // ticket creation
        // @action ticket type
        $item->action="New";
        //    Mail::to('Sai.Bhargav@flcourts18.org')->send(new NewTicket($item));
        Mail::to(backpack_user()->email)->send(new TicketSubmited($item));
        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
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
            // Mail::to($this->crud->getCurrentEntry()->email)->send(new NewAttorneyPassword($this->crud->getCurrentEntry()));


        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest($request)
        );
        $this->data['entry'] = $this->crud->entry = $item;
         // ticket status change
        // @action ticket type
        $item->action="Update";
        if($item->status->name =="In Progress"){
            Mail::to($item->user->email)->send(new TicketUpdate($item));
        } 
        if($item->status->name =="Close"){
            Mail::to($item->user->email)->send(new TicketSloved($item));
        } 
    //    echo  $item->user->email;exit;
        //

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function reset(){

          }

    /**
     * Default behaviour for the Show Operation, in case none has been
     * provided by including a setupShowOperation() method in the CrudController.
     */
    protected function autoSetupShowOperation()
    {
        CRUD::addColumn(['name' => 'ticket_number']);
        CRUD::addColumn(['name' => 'subject']);
        CRUD::addColumn(['name' => 'issue']);
        CRUD::addcolumn([
            'name' => 'priority_id',
            'label' => 'Priority',
            'type' => 'relationship',
            'entity' => 'priority',
            'model' => 'App\Models\TicketsPriority',
            'attribute' => 'name'
        ]);

        CRUD::addcolumn([
            'name' => 'status_id',
            'label' => 'Status',
            'type' => 'relationship',
            'entity' => 'status',
            'model' => 'App\Models\TicketStatus',
            'attribute' => 'name'
        ]);
        CRUD::addColumn([
            'name'              => 'created_at',
            'label'             => 'Date/Time',
            'type'              => 'datetime',
            'format'            => 'DD.MM.Y - H:mm',
        ]);

        CRUD::addColumn([
            'name'              => 'comment',
            'label'             => 'Comment'
        ]);
        CRUD::addColumn([
            'name'              => 'file',
            'label'             => 'file',
            'type'              => 'image'
        ]);

        $this->removeColumnsThatDontBelongInsideShowOperation();
    }

    public function ticketsStatusSelect(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = TicketStatus::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = TicketStatus::paginate(10);
        }

        return $results;
    }

    public function ticketsPrioritySelect(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = TicketsPriority::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = TicketsPriority::paginate(10);
        }

        return $results;
    }

}
