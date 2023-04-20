<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManualAcceptanceController extends Controller
{
    public function store(Project $project, Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer',
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
