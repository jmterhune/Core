<?php

namespace App\Providers;

use App\Ldap\Listeners\ObjectCreated;
use App\Ldap\Listeners\ObjectDeleted;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Container;
use LdapRecord\Laravel\Events\Import\Imported;
use LdapRecord\Models\Events\Created;
use LdapRecord\Models\Events\Deleted;

class LdapEventServiceProvider extends ServiceProvider
{
    /**
     * The LDAP event listener mappings for the application.
     *
     * @return array
     */
    protected $listen = [
        Imported::class => [
            ObjectCreated::class
        ],
        Deleted::class => [
            ObjectDeleted::class
        ],
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $dispatcher = Container::getEventDispatcher();

        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
}
