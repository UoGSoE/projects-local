<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use Illuminate\Http\Request;

class ProjectAcceptanceController extends Controller
{
    public function store($id, Request $request)
    {
        $request->validate([
            'students' => 'required|array|min:1',
        ]);

        $project = Project::findOrFail($id);

        $this->authorize('accept-students', $project);

        $students = User::students()->findMany($request->students);
        $students->each(function ($student) use ($project) {
            $this->authorize('accept-onto-project', [$student, $project]);
            $project->accept($student);
        });

        return redirect(route('project.show', $project->id))->with('success', 'Students Accepted');
    }
}
