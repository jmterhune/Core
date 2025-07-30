<?php

namespace App\Ldap\Listeners;

use App\Models\CourtPermission;
use LdapRecord\Laravel\Events\Import\Deleted;


class ObjectDeleted
{

    public function handle(Deleted $event)
    {
        $user = $event->eloquent;

        // Remove all Court Permissions from Deleted user
        $permissions = CourtPermission::where('user_id', $user->id)->get();

        if($permissions != null){
            foreach ($permissions as $permission){
                $permission->delete();
            }
        }
    }
}
