<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use Illuminate\Http\Request;

class BulkRemovalController extends Controller
{
    public function undergrads()
    {
        Project::undergrad()->get()->each->deleteStudents();

        return redirect()->back()->with('success', 'Undergrads removed');
    }

    public function postgrads()
    {
        Project::postgrad()->get()->each->deleteStudents();

        return redirect()->back()->with('success', 'Postgrads removed');
    }

    public function all()
    {
        User::students()->get()->each->delete();

        return redirect()->back()->with('success', 'All students removed');
    }
}
