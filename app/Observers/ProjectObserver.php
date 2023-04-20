<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    public function created(Project $project): void
    {
    }

    public function updated(Project $project): void
    {
    }

    public function deleting(Project $project)
    {
    }
}
