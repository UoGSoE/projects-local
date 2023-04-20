<?php

namespace App\Listeners;

use Illuminate\Events\Dispatcher;
use App\Events\SomethingNoteworthyHappened;

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function onUserLogin($event)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Logged in'));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'App\Listeners\UserEventSubscriber@onUserLogin'
        );
    }
}
