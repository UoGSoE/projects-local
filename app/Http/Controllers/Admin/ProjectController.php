<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.project.index', [
            'projects' => Project::orderBy('title')->withCount([
                'students', 'students as accepted_students_count' => function ($query) {
                    return $query->where('is_accepted', '=', true);
                }
            ])->get(),
        ]);
    }
}
