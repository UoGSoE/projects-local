<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index($category = 'undergrad')
    {
        $projects = Project::where('category', '=', $category)
            ->orderBy('title')
            ->with(['owner', 'secondSupervisor', 'courses'])
            ->withCount([
                'students',
                'students as accepted_students_count' => function ($query) {
                    return $query->where('is_accepted', '=', true);
                }
            ])
            ->get();

        $projects->each->append('course_codes');

        return view('admin.project.index', [
            'category' => $category,
            'projects' => $projects,
        ]);
    }
}
