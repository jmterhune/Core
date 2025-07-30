<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\PermissionCrudController as PackagePermissionCrudController;

class PermissionCrudController extends PackagePermissionCrudController
{
    public function setup()
    {
        $this->role_model = $role_model = config('backpack.permissionmanager.models.role');
        $this->permission_model = $permission_model = config('backpack.permissionmanager.models.permission');

        $this->crud->setModel($permission_model);
        $this->crud->setEntityNameStrings(trans('backpack::permissionmanager.permission_singular'), trans('backpack::permissionmanager.permission_plural'));
        $this->crud->setRoute(backpack_url('permission'));

        if(!backpack_user()->hasRole('System Admin')){
            $this->crud->denyAccess([ 'create','list','show','update','delete','revise']);
        }

        $this->crud->set('help',
            'This section defines programmatic permissions within the application. <br>
            The modify attorneys permission allows the modification and creation of attorney accounts.'
        );
    }
}
