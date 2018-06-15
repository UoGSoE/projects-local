<?php

namespace App\Observers;

use App\Project;
use App\Events\SomethingNoteworthyHappened;

class ProjectObserver
{
    public function created(Project $project)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Created project {$project->title}"));
    }

    public function updated(Project $project)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Updated project {$project->title}"));
    }

    public function deleting(Project $project)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted project {$project->title}"));
    }

}
