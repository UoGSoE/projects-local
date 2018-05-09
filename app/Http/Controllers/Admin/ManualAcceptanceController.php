<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ManualAcceptanceController extends Controller
{
    public function store(Project $project, Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer'
        ]);

        $student = User::findOrFail($request->student_id);
        $project->addAndAccept($student);

        session()->flash('success', 'Student Accepted');

        return response()->json([
            'message' => 'Student accepted',
        ]);
    }
}
