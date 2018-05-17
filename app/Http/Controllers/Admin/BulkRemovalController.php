<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Course;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BulkRemovalController extends Controller
{
    public function undergrads()
    {
        Course::undergrad()->get()->each->removeAllStudents();
        Project::undergrad()->get()->each->removeAllStudents();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'removed']);
        }
        return redirect()->back()->with('success', 'Undergrads removed');
    }

    public function postgrads()
    {
        Course::postgrad()->get()->each->removeAllStudents();
        Project::postgrad()->get()->each->removeAllStudents();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'removed']);
        }
        return redirect()->back()->with('success', 'Postgrads removed');
    }

    public function all()
    {
        User::students()->get()->each->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'removed']);
        }
        return redirect()->back()->with('success', 'All students removed');
    }
}
