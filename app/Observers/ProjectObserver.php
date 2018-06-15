<?php

namespace App\Observers;

use App\Project;
use App\Events\SomethingNoteworthyHappened;

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
