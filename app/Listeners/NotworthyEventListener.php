<?php

namespace App\Listeners;

use App\Events\SomethingNoteworthyHappened;

class NotworthyEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SomethingNoteworthyHappened $event): void
    {
        if ($event->who) {
            activity()
                ->causedBy($event->who)
                ->log($event->what);
        } else {
            activity()
                ->log($event->what);
        }
    }
}
