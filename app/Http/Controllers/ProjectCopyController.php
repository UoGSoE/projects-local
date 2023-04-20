<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class ProjectCopyController extends Controller
{
    public function create($id): View
    {
        $project = Project::findOrFail($id);

        $copyProject = $project->replicate();

        if ($copyProject->isUndergrad()) {
            $copyProject->category = 'postgrad';
        } else {
            $copyProject->category = 'undergrad';
        }

        return view('project.create', [
            'project' => $copyProject,
            'programmes' => Programme::where('category', '=', $copyProject->category)->orderBy('title')->get(),
            'courses' => Course::where('category', '=', $copyProject->category)->orderBy('title')->get(),
            'staff' => User::staff()->orderBy('surname')->get(),
        ]);
    }
}
