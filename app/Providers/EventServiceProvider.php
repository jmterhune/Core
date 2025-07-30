<?php

namespace App\Providers;

use App\Ldap\Listeners\ObjectCreated;
use App\Ldap\Listeners\ObjectDeleted;
use App\Models\CourtTemplateOrder;
use App\Observers\CourtTemplateObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use LdapRecord\Container;
use LdapRecord\Laravel\Events\Import\Deleted;
use LdapRecord\Laravel\Events\Import\Imported;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Imported::class => [
            ObjectCreated::class
        ],
        Deleted::class => [
            ObjectDeleted::class
        ],
    ];

    /**
     * Register any events for your application.
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

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
