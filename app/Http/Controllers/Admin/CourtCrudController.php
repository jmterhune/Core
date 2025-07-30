<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourtRequest;
use App\Models\CourtEventTypes;
use App\Models\CourtMotions;
use App\Models\CourtPermission;
use App\Models\CourtTemplateOrder;
use App\Models\CourtTimeslot;
use App\Models\Judge;
use App\Models\Template;


use App\Models\TemplateTimeslot;
use App\Models\UserDefinedFields;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\ReviseOperation\ReviseOperation;
use Carbon\Carbon;


/**
 * Class CourtCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CourtCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Court::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/court');
        CRUD::setEntityNameStrings('court', 'courts');

        // Default Order by
        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('description','asc');
        }

        // Authorization
        if(!backpack_user()->hasAnyRole(['System Admin', 'JA'])){
            $this->crud->denyAccess(['create','list','show','update','delete','revise']);
        }

        if(backpack_user()->hasRole([ 'JA'])){
            $this->crud->denyAccess(['revise','delete','create']);

            $this->crud->addClause('whereIn','id',backpack_user()->courts());
        }

        $this->crud->denyAccess('show');

        $this->crud->set('help',
            'This section is the culmination of every other section. <br> From here a user can manipulate the court and it\'s calendar.'
        );

        $this->crud->set('edit.help',
            'This section is the culmination of every other section. <br> From here a user can manipulate the court and it\'s calendar.'
        );

        $this->crud->set('create.help',
            'In addition to the fields marked required, you must also fill out motions and hearing types on the Scheduling Tab.'
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
        $this->crud->addButtonFromView('line', 'calendar', 'calendar', 'end');
//        $this->crud->addButtonFromView('line', 'templates','templates' ,'end');
//        $this->crud->addButtonFromView('line', 'truncate','truncate' ,'end');
        $this->crud->addButton('line', 'revise', 'view', 'revise-operation::revise_button', 'end');
        CRUD::column('description');
        CRUD::addcolumn([
            'name' => 'judge',
            'label' => 'Judge',
            'type' => 'relationship',
            'entity' => 'judge',
            'model' => 'App\Models\Judge',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('judge', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            },
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->join('judges', 'courts.id', '=','judges.court_id')
                    ->orderBy('name', $columnDirection)->select('courts.*');
            }
        ]);
        CRUD::addcolumn([
            'name' => 'county_id',
            'label' => 'County',
            'type' => 'relationship',
            'entity' => 'county',
            'model' => 'App\Models\County',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('county', function ($q) use ($column, $searchTerm) {
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
        Widget::add()->type('script')->content('assets/js/admin/forms/court.js');

        CRUD::setValidation(CourtRequest::class);

        if(backpack_user()->hasRole(['System Admin'])){
            CRUD::field('description')->tab('Main')->wrapper(['class' => 'form-group col-md-6']);
            CRUD::field('case_format_type')->tab('Main')->type('hidden');
            CRUD::addfield([
                'name' => 'county_id',
                'label' => 'County',
                'type' => 'relationship',
                'entity' => 'county',
                'model' => 'App\Models\County',
                'attribute' => 'name',
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'tab' => 'Main'
            ]);
            $this->crud->addField([
                'label'     => "Case Num Format",
                'type'      => 'case_format',
                'name'      => 'case_num_format', // the db column for the foreign key
                'wrapper' => [
                    'class' => 'from-group col-md-6'
                ],
                'tab' => 'Main'
            ]);
            $this->crud->addField([
                'label'     => "Default Prosecuting Attorney",
                'type'      => 'select2_from_ajax',
                'name'      => 'def_attorney_id', // the db column for the foreign key
                'entity'    => 'attorney', // the method that defines the relationship in your Model
                'model' => 'App\Models\Attorney',
                'data_source' => url('api/attorney'),
                'attribute' => 'name', // foreign key attribute that is shown to user
                'wrapper' => [
                    'class' => 'from-group col-md-6'
                ],
                'tab' => 'Main'
            ]);
            $this->crud->addField([
                'name' => 'plaintiff',
                'label' => 'Default Plaintiff',
                'wrapper' => [
                    'class' => 'from-group col-md-6 '
                ],
                'tab' => 'Main'
            ]);
            $this->crud->addField([
                'label'     => "Default Opposing Attorney",
                'type'      => 'select2_from_ajax',
                'name'      => 'opp_attorney_id', // the db column for the foreign key
                'entity'    => 'opp_attorney', // the method that defines the relationship in your Model
                'data_source'     => url('api/attorney'),
                'attribute' => 'name', // foreign key attribute that is shown to user
                'wrapper' => [
                    'class' => 'from-group col-md-6 mt-3'
                ],
                'tab' => 'Main'
            ]);
            $this->crud->addField([
                'name' => 'defendant',
                'label' => 'Default Defendant',
                'wrapper' => [
                    'class' => 'from-group col-md-6 mt-3'
                ],
                'tab' => 'Main'
            ]);


        }



        // Show Available Timeslots on Internet
        $this->crud->addField([
            'name' => 'email_confirmations',
            'label' => 'Email Confirmations',
            'type' => 'switch',
            'tab' => 'Scheduling',
            'color' => 'primary',
            'onLabel' => 'On',
            'offLabel' => 'Off',
            'wrapper' => [
                'class' => 'form-group pt-4 col-md-4'
            ]
        ]);

        if(backpack_user()->hasRole(['JA'])){
            $this->crud->addField([
                'name' => 'description',
                'type' => 'hidden',
                'tab' => 'Scheduling',
            ]);
            $this->crud->addField([
                'label'     => "Case Num Format",
                'type'      => 'hidden',
                'name'      => 'case_num_format', // the db column for the foreign key
                'tab' => 'Scheduling'
            ]);
        }



        $this->crud->addField([
            'name' => 'custom_email_body',
            'label' => 'Email Template',
            'type'  => 'summernote',
            'wrapper' => [
                'class' => 'from-group col-md-12 mt-3'
            ],

            'tab' => 'Custom Email'
        ]);

        $this->crud->addField([
            'name' => 'Available Tags ',
            'label' => 'Email Template',
            'type'  => 'custom_html',
            'wrapper' => [
                'class' => 'from-group col-md-12 mt-3'
            ],
            'value' => ' <li>Case Number: [case]</li>
                <li>Motion: [motion]</li>
                <li>Attorney: [attorney]</li>
                <li>Plaintiff: [plaintiff]</li>',
            'tab' => 'Custom Email'
        ]);


        // Week on Calendar
        $this->crud->addField([
            'name' => 'calendar_weeks',
            'label' => 'Weeks on Calendar',
            'type' => 'number',
            'tab' => 'Scheduling',
            'default' => 0,
            'wrapper' => [
                'class' => 'form-group col-md-3 required'
            ],

        ]);


        $this->crud->addField([
            'name' => 'auto_extension',
            'label' => 'Extending Calendar',
            'type' => 'radio',
            'inline' => true,
            'default' => true,
            'options' => [
                true => 'Automatic',
                false => 'Manual'
            ],
            'tab' => 'Scheduling',
            'wrapper' => [
                'class' => 'form-group col-md-4 required'
            ]
        ]);


        $this->crud->addField([
            'name'     => 'public_header',
            'label'    => 'Custom HTML',
            'type'     => 'custom_html',
            'value'    => '<h5 class="text-primary mt-4">Public Settings</h5><hr />',
            'tab' => 'Scheduling'
        ]);

        if(!isset($this->crud->getCurrentEntry()->judge)){
            $this->crud->addField([
                'name' => 'scheduling',
                'label' => 'Allow Web Scheduling <i class="la-lg la la-question-circle text-primary" data-toggle="tooltip" data-placement="top" title="Disabled if there is no Judge attached to the court."></i>',
                'type' => 'switch',
                'tab' => 'Scheduling',
                'onLabel' => 'On',
                'offLabel' => 'Off',
                'attributes' => ['disabled' => 'disabled'],
                'wrapper' => [
                    'class' => 'form-group col-md-12'
                ]
            ]);
        } else{
            $this->crud->addField([
                'name' => 'scheduling',
                'label' => 'Allow Web Scheduling',
                'type' => 'switch',
                'tab' => 'Scheduling',
                'color' => 'primary',
                'onLabel' => 'On',
                'offLabel' => 'Off',
                'wrapper' => [
                    'class' => 'form-group col-md-12'
                ]
            ]);
        }


        // Show Available Timeslots on Internet
        $this->crud->addField([
            'name' => 'public_timeslot',
            'label' => 'Public Available Timeslots',
            'type' => 'switch',
            'tab' => 'Scheduling',
            'color' => 'primary',
            'onLabel' => 'On',
            'offLabel' => 'Off',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        // Lagtime for Search of Available Timeslots
        $this->crud->addField([
            'name' => 'lagtime',
            'label' => 'Lagtime for Available Timeslots',
            'type' => 'number',
            'tab' => 'Scheduling',
            'wrapper' => [
                'class' => 'form-group col-md-3'
            ]
        ]);

        // Lagtime for Search of Available Timeslots
        $this->crud->addField([
            'name' => 'max_lagtime',
            'label' => 'Max Available Time Slots',
            'type' => 'number',
            'tab' => 'Scheduling',
            'wrapper' => [
                'class' => 'form-group col-md-3'
            ]
        ]);


        // Show Docket on Internet
        $this->crud->addField([
            'name' => 'public_docket',
            'label' => 'Show Docket on Internet',
            'type' => 'switch',
            'tab' => 'Scheduling',
            'color' => 'primary',
            'onLabel' => 'On',
            'offLabel' => 'Off',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);

        // # of Days of Docket on Internet
        $this->crud->addField([
            'name' => 'public_docket_days',
            'label' => 'Number of Days of Docket on Internet',
            'type' => 'number',
            'tab' => 'Scheduling',
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ]
        ]);



        $this->crud->addField([
            'name'     => 'required_header',
            'label'    => 'Custom HTML',
            'type'     => 'custom_html',
            'value'    => '<h5 class="text-primary mt-4">Required Fields</h5><hr />',
            'tab' => 'Scheduling'
        ]);

        $this->crud->addField([
            'name'          => 'plaintiff_required',
            'type'          => "switch",
            'color' => 'primary',
            'onLabel' => 'Yes',
            'offLabel' => 'No',
            'wrapper' => [
                'class' => 'from-group col-md-6 '
            ],
            'tab' => 'Scheduling',
        ]);

        $this->crud->addField([
            'name'          => 'defendant_required',
            'type'          => "switch",
            'color' => 'primary',
            'onLabel' => 'Yes',
            'offLabel' => 'No',
            'wrapper' => [
                'class' => 'from-group col-md-6 '
            ],
            'tab' => 'Scheduling',
        ]);

        $this->crud->addField([
            'name'          => 'plaintiff_attorney_required',
            'type'          => "switch",
            'color' => 'primary',
            'onLabel' => 'Yes',
            'offLabel' => 'No',
            'wrapper' => [
                'class' => 'from-group col-md-6 mt-3'
            ],
            'tab' => 'Scheduling',
        ]);

        $this->crud->addField([
            'name'          => 'defendant_attorney_required',
            'type'          => "switch",
            'color' => 'primary',
            'onLabel' => 'Yes',
            'offLabel' => 'No',
            'wrapper' => [
                'class' => 'from-group col-md-6 mt-3'
            ],
            'tab' => 'Scheduling',

        ]);


        $this->crud->addField([
            'name'     => 'tab2',
            'label'    => 'Custom HTML',
            'type'     => 'custom_html',
            'value'    => '<hr />',
            'tab' => 'Scheduling'
        ]);


        $this->crud->addField([
            'name' => 'motions',
            'label' => 'Available Motions',
            'type' => 'relationship',
            'entity' => 'motions',
            'options' => (function ($query) {
                return $query->orderBy('description', 'asc')->get();
            }),
            'tab' => 'Scheduling'
        ]);

        $this->crud->addField([
            'name' => 'restricted_motions',
            'label' => 'Restricted Motions',
            'type' => 'relationship',
            'entity'  => 'restricted_motions',
            'options' => (function ($query) {
                return $query->orderBy('description', 'asc')->get();
            }),
            'hint' => 'Attorneys will be unable to select the above motions on all timeslots',
            'tab' => 'Scheduling',
            'wrapper' => [
                'class' => 'col-md-12 my-4'
            ],

        ]);
        $this->crud->addField([
            'name' => 'event_types',
            'label' => 'Attorney Scheduling Available Hearing Types',
            'type' => 'relationship',
            'hint' => 'Attorneys will only be able to select the above hearing type(s) when scheduling',
            'options' => (function ($query) {
                return $query->orderBy('name', 'asc')->get();
            }),
            'tab' => 'Scheduling'
        ]);

        $this->crud->addField([
            'name'     => 'Web Policy Header',
            'label'    => 'Custom HTML',
            'type'     => 'custom_html',
            'value'    => '<hr />',
            'tab' => 'Scheduling'
        ]);
        $this->crud->addField([
            'name'  => 'web_policy',
            'label' => 'Web Policy',
            'type'  => 'summernote',
            'options' => [],
            'tab' => 'Scheduling'
        ]);

        if(!Template::where('court_id', $this->crud->getCurrentEntryId())->get()->isEmpty()){
            if($this->crud->getCurrentEntry()->auto_extension){
                $this->crud->addField([
                    'name'          => 'template_order_auto',
                    'label'         => 'Automatic',
                    'type'          => "relationship",
                    // ..

                    'subfields'   => [
                        [
                            'name' => 'order',
                            'label' => 'Week',
                            'type' => 'number',
                            'wrapper' => [
                                'class' => 'form-group col-md-3 required',
                            ],
                            'attributes' => [ 'required' => 'required']
                        ],
                        [
                            'name' => 'template',
                            'label' => 'Template',
                            'attribute' => 'name',
                            'type' => 'relationship',
                            'wrapper' => [
                                'class' => 'form-group col-md-9',
                            ],
                            'options'   => (function ($query) {
                                return $query->whereIn('court_id', backpack_user()->courts())
                                    ->where('court_id', $this->crud->getCurrentEntryId());
                            }),
                        ],
                        [
                            'name' => 'auto',
                            'type' => 'hidden',
                            'value' => 1
                        ]
                    ],
                    'tab' => 'Templates'
                ]);
            } else{
                $this->crud->addField([
                    'name'          => 'template_order_manual',
                    'label'         => 'Manual',
                    'type'          => "relationship_custom",
                    // ..
                    'subfields'   => [
                        [   // date_picker
                            'name'  => 'date',
                            'type'  => 'text',
                            'label' => 'Week',
                            'wrapper' => [
                                'class' => 'form-group col-md-6',
                            ],
                            'attributes' => ['disabled' => 'disabled'],
                            // optional:
                        ],
                        [
                            'name' => 'template',
                            'label' => 'Template',
                            'attribute' => 'name',
                            'type' => 'relationship',
                            'wrapper' => [
                                'class' => 'form-group col-md-6',
                            ],
                            'options'   => (function ($query) {
                                return $query->whereIn('court_id', backpack_user()->courts())
                                    ->where('court_id', $this->crud->getCurrentEntryId());
                            }),
                        ],
                        [
                            'name' => 'auto',
                            'type' => 'hidden',
                            'value' => 0
                        ]
                    ],
                    'tab' => 'Templates'
                ]);
            }
	}
	if(backpack_user()->hasRole(['JA','System Admin'])){
                $this->crud->addField([
                    'name' => 'timeslot_header',
                    'label' => 'Custom Header',
                    'type'  => 'summernote',
                    'wrapper' => [
                        'class' => 'from-group col-md-12 mt-3'
                    ],

                    'tab' => 'Timeslot Search Header'
                ]);
            }

        $this->crud->addField([
            'name' => 'custom_header',
            'label' => 'Custom Docket Print Header',
            'type'  => 'textarea',
            'wrapper' => [
                'class' => 'from-group col-md-12 mt-3'
            ],

            'tab' => 'Docket Print Header'
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
     * Default behaviour for the Show Operation, in case none has been
     * provided by including a setupShowOperation() method in the CrudController.
     */
    protected function autoSetupShowOperation()
    {
        CRUD::addColumn('description');
        CRUD::addColumn('case_num_format');
        CRUD::addColumn([
            'name' => 'def_attorney_id',
            'type' => 'relationship',
            'label' => 'Default Attorney',
            'entity' => 'attorney',
            'attribute' => 'name',
            'model' => 'App\Models\Attorney',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('attorney', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        CRUD::addColumn('plaintiff');
        CRUD::addColumn([
            'name' => 'opp_attorney_id',
            'type' => 'relationship',
            'label' => 'Default Opposing Attorney',
            'entity' => 'opp_attorney',
            'attribute' => 'name',
            'model' => 'App\Models\Attorney',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('opp_attorney', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        CRUD::addColumn('defendant');

        // if the model has timestamps, add columns for created_at and updated_at
        if ($this->crud->get('show.timestamps') && $this->crud->model->usesTimestamps()) {
            $this->crud->column($this->crud->model->getCreatedAtColumn())->type('datetime');
            $this->crud->column($this->crud->model->getUpdatedAtColumn())->type('datetime');
        }

        // if the model has SoftDeletes, add column for deleted_at
        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->crud->column($this->crud->model->getDeletedAtColumn())->type('datetime');
        }

        // remove the columns that usually don't make sense inside the Show operation
        $this->removeColumnsThatDontBelongInsideShowOperation();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $element = $this->crud->getEntry($id);
        $permission = isset($element->judge)
            ? CourtPermission::where('judge_id', $element->judge->id)->where('user_id',backpack_user()->id)->first()
            : false;

        if (backpack_user()->hasrole('System Admin') || $permission->editable) {
            $this->crud->allowAccess(['update']);
        } else {
            $this->crud->denyAccess(['update']);
        }

        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
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

        CourtMotions::where('court_id', $id)->delete();
        CourtEventTypes::where('court_id', $id)->delete();
        CourtTemplateOrder::where('court_id', $id)->delete();

        $templates = Template::where('court_id', $id)->get();
        foreach ($templates as $template){
            TemplateTimeslot::where('court_template_id', $template->id)->delete();
            $template->delete();
        }

        Judge::where('court_id', $id)->update([
            'court_id' => null
        ]);

        UserDefinedFields::where('court_id', $id)->delete();
        CourtTimeslot::where('court_id',$id)->delete();
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
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

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $request->except(['motions','restricted_motions','_token','_method','_http_referrer','id','_current_tab','_save_action'])
        );
        $this->data['entry'] = $this->crud->entry = $item;

        if($request->auto_extension == 0){
            for($x = 0; $x < $request->calendar_weeks; $x++){
                CourtTemplateOrder::firstOrCreate([
                    'court_id' => $this->crud->getCurrentEntryId(),
                    'date' => Carbon::now()->addWeeks($x)->startOfWeek(),
                    'auto' => 0
                ]);
            }
        }

        //dd($request->all(), $this->crud->getCurrentEntryId());
        if($request->restricted_motions){
            foreach($request->restricted_motions as $res_motion){
                CourtMotions::updateorcreate([
                    'court_id' => $this->crud->getCurrentEntryId(),
                    'motion_id' => $res_motion,
                    'allowed' => false
                ]);
            }
            CourtMotions::where('court_id', $this->crud->getCurrentEntryId())
                ->where('allowed', false)->wherenotin('motion_id', $request->restricted_motions)->delete();
        } else {
            CourtMotions::where('court_id', $this->crud->getCurrentEntryId())
                ->where('allowed', false)->delete();
        }

        if($request->motions) {
            foreach ($request->motions as $motion) {
                CourtMotions::updateorcreate([
                    'court_id' => $this->crud->getCurrentEntryId(),
                    'motion_id' => $motion,
                    'allowed' => true
                ]);
            }
            CourtMotions::where('court_id', $this->crud->getCurrentEntryId())
                ->where('allowed', true)->wherenotin('motion_id', $request->motions)->delete();
        } else{
            CourtMotions::where('court_id', $this->crud->getCurrentEntryId())
                ->where('allowed', true)->delete();
        }

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
public function getCategory($courtId){
        try{
        //    $court = \App\Models\Court::select('category_print')->find($courtId);
		//  return $court->category_print;
		 $court = \App\Models\Court::select('category_print','custom_header')->find($courtId);
            return ['category_print' => $court->category_print,'custom_header' => $court->custom_header];

        } catch(\ErrorException){
            return false;
        }
    }
}
