<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Course;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class BulkRemovalController extends Controller
{
    public function undergrads()
    {
        Course::undergrad()->get()->each->removeAllStudents();
        Project::undergrad()->get()->each->removeAllStudents();

        event(new SomethingNoteworthyHappened(auth()->user(), 'Removed all undergrad students'));

        if (request()->wantsJson()) {
            return response()->json(['message' => 'removed']);
        }
        return redirect()->back()->with('success', 'Undergrads removed');
    }

    public function postgrads()
    {
        Course::postgrad()->get()->each->removeAllStudents();
        Project::postgrad()->get()->each->removeAllStudents();

        event(new SomethingNoteworthyHappened(auth()->user(), 'Removed all postgrad students'));

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
