<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

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
            'Bulk accepted '.count($request->students).' students'
        ));

        return redirect()->back()->with('success', 'Students Accepted');
    }
}
