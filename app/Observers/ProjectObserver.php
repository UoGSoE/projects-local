<?php

namespace App\Observers;

use App\Events\SomethingNoteworthyHappened;
use App\Project;

class ProjectObserver
{
    public function created(Project $project)
    {
    }

    public function updated(Project $project)
    {
    }

    public function deleting(Project $project)
    {
    }
}
