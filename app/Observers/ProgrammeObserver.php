<?php

namespace App\Observers;

use App\Programme;
use App\Events\SomethingNoteworthyHappened;

class ProgrammeObserver
{
    public function created(Programme $programme)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Created programme {$programme->title}"));
    }

    public function updated(Programme $programme)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Updated programme {$programme->title}"));
    }

    public function deleting(Programme $programme)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted programme {$programme->title}"));
    }
}
