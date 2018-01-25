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
        $students = User::students()->findMany($request->students);
        $students->each(function ($student) use ($project) {
            $student->projects()->sync([$project->id => ['is_accepted' => true]]);
        });

        return redirect(route('project.show', $project->id))->with('success', 'Students Accepted');
    }
}
