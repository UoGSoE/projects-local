<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class BulkAcceptanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'students' => 'required|array',
        ]);

        collect($request->students)->each(function ($projectId, $studentId) {
            $student = User::findOrFail($studentId);
            $project = Project::findOrFail($projectId);
            $project->accept($student);
        });

        event(new SomethingNoteworthyHappened(
            $request->user(),
            "Bulk accepted " . count($request->students) . " students"
        ));

        return redirect()->back()->with('success', 'Students Accepted');
    }
}
