<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class ManualAcceptanceController extends Controller
{
    public function store(Project $project, Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer'
        ]);

        $student = User::findOrFail($request->student_id);
        $project->addAndAccept($student);

        event(new SomethingNoteworthyHappened(
            $request->user(),
            "Manually accepted student {$student->matric} onto project {$project->title}"
        ));

        session()->flash('success', 'Student Accepted');

        return response()->json([
            'message' => 'Student accepted',
        ]);
    }
}
