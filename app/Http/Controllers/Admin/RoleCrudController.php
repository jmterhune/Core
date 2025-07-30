<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as PackageRoleCrudController;


class RoleCrudController extends PackageRoleCrudController
{

    public function setup()
    {

        $this->role_model = $role_model = config('backpack.permissionmanager.models.role');
        $this->permission_model = $permission_model = config('backpack.permissionmanager.models.permission');

        $this->crud->setModel($role_model);
        $this->crud->setEntityNameStrings(trans('backpack::permissionmanager.role'), trans('backpack::permissionmanager.roles'));
        $this->crud->setRoute(backpack_url('role'));

        if(!backpack_user()->hasRole('System Admin')){
            $this->crud->denyAccess([ 'create','list','show','update','delete','revise']);
        }
        $this->crud->setSubheading('some string', 'list');

        $this->crud->set('help',
            'This section defines programmatic rules within the application. <br>
            System Admins Role has access to all sections. JA Role only has access to judges and courts that have been assigned to the user. <br>
            This also defines which permissions a Role automatically inherits. <br>'
        );
    }

}
