<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Project;
use App\User;
use Illuminate\Http\Request;

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
