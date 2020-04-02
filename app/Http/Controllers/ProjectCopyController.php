<?php

namespace App\Http\Controllers;

use App\Course;
use App\Programme;
use App\Project;
use App\User;
use Illuminate\Http\Request;

class ProjectCopyController extends Controller
{
    public function create($id)
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
