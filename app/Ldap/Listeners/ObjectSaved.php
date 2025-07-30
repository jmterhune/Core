<?php

namespace App\Ldap\Listeners;

use LdapRecord\Models\Events\Saved;

class ObjectSaved
{
    public function handle(Saved $event)
    {
        $objectName = $event->getModel()->getName();

        // Send an email when the object has been modified.
        Mail::raw("Object [$objectName] has been modified.", function ($message) {
            $message->from('notifier@company.com', 'LDAP Notifier');
            $message->to('it-support@company.com');
            $message->subject('LDAP Object Modified');
        });
    }
}
