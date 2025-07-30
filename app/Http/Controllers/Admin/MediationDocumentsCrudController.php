<?php

namespace App\Http\Controllers\Admin;
use App\Models\MediationDocuments;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\ReviseOperation\ReviseOperation;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\MediationDocumentsRequest;
use Response;

/**
 * Class AvailableScheduleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MediationDocumentsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use ReviseOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\MediationDocuments::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/mediation/documents');
        CRUD::setEntityNameStrings('documents', 'documents');

        CRUD::setValidation(MediationDocumentsRequest::class);

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin'])){
            $this->crud->denyAccess(['revise']);
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
        $this->crud->setDefaultPageLength(50);
        $this->crud->setPageLengthMenu([50, 100, 200, 300]);
        $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'end');
        $this->crud->addButton('line', 'revise', 'view', 'revise-operation::revise_button', 'end');
        $this->crud->denyAccess(['delete']);
        // $this->crud->enableExportButtons();
        
        CRUD::column('d_title')->label('Title');
        
        CRUD::column('d_valid_date')->label('Valid Date')->type('closure')->function(function($entry) {
            return date('Y-m-d',strtotime($entry->at_begin));
        });

        CRUD::column('d_ext')->label('Extension');
        CRUD::column('d_original')->label('Original Name');
        CRUD::column('d_u_id')->label('Created By')->type('select')->entity('admin')->model('App\Models\User')->attribute('name');
        CRUD::column('d_fname')->label('First Name');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::field('d_original')
            ->type('upload')
            ->label("File")
            ->upload(true)
            ->withFiles([
                'disk' => 'public', // the disk where file will be stored
                'path' => 'mediation/documents', // the path inside the disk where file will be stored
        ],'both');

        CRUD::field('d_title')->type('text')->label("Title")->wrapper(['class' => ' form-group col-md-7']);

        CRUD::field('d_valid_date')->type('date')->label("Valid Date")->wrapper(['class' => ' form-group col-md-7']);
        
        CRUD::field('d_fname')->type('text')->label("File Name")->wrapper(['class' => ' form-group col-md-7']);
        
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
        Storage::putFile('mediation/documents/'.$request->file('d_original')->getClientOriginalName(), $request->file('d_original'));
        $document = new MediationDocuments;
        $document->d_title = $request->get('d_title');
        $document->d_valid_date = $request->get('d_valid_date');
        $document->d_ext = $request->file('d_original')->extension();
        $document->d_original = $request->file('d_original')->getClientOriginalName();
        $document->d_u_id = auth()->user()->id;
        $document->d_fname = $request->get('d_fname');
        $document->save();
        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();
        return Redirect::to($this->crud->route);
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

    public function update(Request $request)
    {
        $request = $this->crud->validateRequest();
        $document = MediationDocuments::find($request->id);
        if($request->file('d_original') != NULL)
        {
            Storage::delete('mediation/documents/'. $document->d_original);
            Storage::putFile('mediation/documents/'.$request->file('d_original')->getClientOriginalName(), $request->file('d_original'));
            
        $document->d_ext = $request->file('d_original')->extension();
        $document->d_original = $request->file('d_original')->getClientOriginalName();
        }

        $document->d_title = $request->get('d_title');
        $document->d_valid_date = $request->get('d_valid_date');
        $document->d_u_id = auth()->user()->id;
        $document->d_fname = $request->get('d_fname');
        $document->save();
        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        return $this->crud->performSaveAction(MediationDocuments::find($request->id));
    }

    public function downloadFile($docId)
    {
        $document = MediationDocuments::find($docId);
        $file_path = storage_path('app/mediation/documents/'). $document->d_original;
        if (file_exists($file_path))
        {
            return Response::download($file_path, $document->d_original, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            exit('Requested file does not exist on our server!');
        }
    }

}

