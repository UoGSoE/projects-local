<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use Illuminate\Http\Request;

class BulkAcceptanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'students' => 'required|array',
        ]);

        collect($request->students)->each(function ($options) {
            $student = User::findOrFail(key($options));
            $project = Project::findOrFail($options[$student->id]);
            $project->accept($student);
        });

        return redirect()->back()->with('success', 'Students Accepted');
    }
}
