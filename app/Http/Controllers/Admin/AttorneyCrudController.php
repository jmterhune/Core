<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AttorneyRequest;
use App\Mail\NewAttorney;
use App\Mail\AttorneyPasswordReset;
use App\Mail\NewAttorneyPassword;
use App\Models\Attorney;
use App\Models\Email;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;


/**
 * Class AttorneyCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AttorneyCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;
    use ReviseOperation;


    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        $this->crud->addButtonFromView('line', 'reset', 'reset', 'beginning');
        CRUD::setModel(\App\Models\Attorney::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/attorney');
        CRUD::setEntityNameStrings('attorney', 'attorneys');

        // Default Order by
        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('name','asc');
        }

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin']) && !backpack_user()->hasPermissionTo('modify attorneys','web')){
            $this->crud->denyAccess(['create','list','show','update','delete','revise']);
        }

        if(backpack_user()->hasAnyRole(['JA'])){
            $this->crud->denyAccess(['show','delete','revise']);
        }


        $this->crud->set('help',
            'All Attorney accounts live here. Attorneys must be enabled manually upon verification of bar number.'
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

        CRUD::addColumn([
            'name'=> 'enabled',
            'label' => 'Status',
            'type' => 'enabled',
            'wrapper' => [
                'class' => 'text-center'
            ],
        ]);
        CRUD::addColumn(['name' => 'name']);
        CRUD::addColumn([
            'name' => 'bar_num',
            'type' => 'barnum',
            'label' => 'Bar Number',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('bar_num', 'like', '%'.$searchTerm.'%');
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
        CRUD::setValidation(AttorneyRequest::class);

        CRUD::addField([
            'name' => 'enabled',
            'label' => 'Account Status',
            'type' => 'radio',
            'options' => [
                1 => 'Enabled',
                0 => 'Disabled'

            ],
            'wrapper' => ['class' => 'form-group col-md-12'],
            'inline' => true,
            'default' => 0
        ]);

        CRUD::addField(['name' => 'name', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'bar_num', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'wrapper' => ['class' => 'form-group col-md-4']]);
        CRUD::addField(['name' => 'notes', 'type' => 'textarea', 'wrapper' => ['class' => 'form-group col-md-12']]);
        CRUD::addField([
            'name' => 'email',
            'label' => 'Email Addresses',
            'entity' => 'email',
            'inline_create' => true,
            'type' => 'repeatable',
            'max_rows' => 3,
            'attribute' => 'email',
            'wrapper' => ['class' => 'form-group col-md-12'],
            'subfields' => [ // also works as: "fields"
                [
                    'name'    => 'email',
                    'type'    => 'text',
                    'label'   => 'Email',
                    'wrapper' => ['class' => 'form-group col-md-12'],
                ],
            ]
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
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();


        $request['password'] = $this->crud->getRequest()->bar_num;

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        $bar_num = ltrim($request->bar_num, 0);


        // insert item in the db
        $item = Attorney::create([
            'name' => $request->name,
            'bar_num' => $bar_num,
            'enabled' => $request->enabled,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'password' => Hash::make($bar_num)
        ]);

        foreach ($request->email as $email){
            Email::create([
                'email' => $email['email'],
                'emailable_type' => 'App\Models\Attorney',
                'emailable_id' => $item->id
            ]);
        }


        $this->data['entry'] = $this->crud->entry = $item;

        //TODO: Create Email entry


//        if($item->enabled){
//            Mail::to($requester_email)->send(new AttorneyPasswordReset($item));
//        }

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->customePerformSaveAction($item->getKey());
    }

    public function customePerformSaveAction($itemId = null)
    {
        return \Redirect::to(route('attorney.index'));
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

        if($request->enabled && !$this->crud->getCurrentEntry()->enabled){
            foreach ($this->crud->getCurrentEntry()->email as $email){
                Mail::to($email->email)->send(new NewAttorneyPassword($this->crud->getCurrentEntry()));
            }
        }

        $request_ids = [];

        if($request->email != null){
            foreach ($request->email as $item){
                $request_ids[] .= $item['id'];
            }

            $clean_request_email_ids = array_filter($request_ids);

            if(!empty($clean_request_email_ids)){
                Email::where('emailable_id', $request->get($this->crud->model->getKeyName()))->whereNotIn('id',$clean_request_email_ids)->delete();
            }

            foreach ($request->email as $email){

                Email::updateOrCreate(['id' => $email['id']],
                    [
                        'email' => $email['email'],
                        'emailable_id' => $request->get($this->crud->model->getKeyName()),
                        'emailable_type' => 'App\Models\Attorney'
                    ]);
            }
        } else{
            Email::where('emailable_id', $request->get($this->crud->model->getKeyName()))->delete();
        }

        $request->request->remove('email');

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

    public function reset(Attorney $id){

        $attorney = $id;

        $attorney->password = Hash::make($attorney->bar_num);
        $attorney->password_changed_at = null;
        $attorney->save();

        // show a success message
        \Alert::success('The Password was reset back to default.')->flash();

        $emails = $id->email()->select('email')->get()->toArray();

        //Send emails notifying of password reset
        Mail::to($this->flatten_array($emails))->send(new AttorneyPasswordReset($attorney));

        return redirect()->back();
    }

    function flatten_array(array $array): array {
        $recursiveArrayIterator = new RecursiveArrayIterator(
            $array,
            RecursiveArrayIterator::CHILD_ARRAYS_ONLY
        );
        $iterator = new RecursiveIteratorIterator($recursiveArrayIterator);

        return iterator_to_array($iterator, false);
    }

    /**
     * Default behaviour for the Show Operation, in case none has been
     * provided by including a setupShowOperation() method in the CrudController.
     */
    protected function autoSetupShowOperation()
    {
        // guess which columns to show, from the database table
//        if ($this->crud->get('show.setFromDb')) {
//            $this->crud->setFromDb(false, true);
//        }

        CRUD::addColumn('name');
        CRUD::addColumn('bar_num');
        CRUD::addColumn('phone');
        CRUD::Column('email')->type('relationship')->entity('email')->model('App\Models\Email')->attribute('email');
        CRUD::Column('scheduling')->type('boolean');
        CRUD::Column('enabled')->type('boolean');
        CRUD::Column('notes')->type('textarea');


        // remove the columns that usually don't make sense inside the Show operation
        $this->removeColumnsThatDontBelongInsideShowOperation();
    }

    public function getAttroneyDetails($id)
    {
        $attroney = Attorney::with('email')->where('id',$id)->first();
        return $attroney;
    }


}
