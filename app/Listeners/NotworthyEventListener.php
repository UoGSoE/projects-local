<?php

namespace App\Listeners;

use App\Events\SomethingNoteworthyHappened;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
     *
     * @param  SomethingNoteworthyHappened  $event
     * @return void
     */
    public function handle(SomethingNoteworthyHappened $event)
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
