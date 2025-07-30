<?php

namespace App\Ldap\Listeners;

use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Events\Import\Imported;
use LdapRecord\Models\Events\Saved;

class ObjectCreated
{
    public function handle(Imported $event)
    {
        $user = $event->eloquent;

        // Add JA Role to new User
        $user->assignRole('JA');
        $user->givePermissionTo('modify attorneys');

    }
}
