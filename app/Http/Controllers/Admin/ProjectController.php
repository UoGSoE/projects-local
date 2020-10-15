<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index($category = 'undergrad')
    {
        $projects = Project::where('category', '=', $category)
            ->orderBy('title')
            ->with(['owner', 'secondSupervisor', 'courses', 'programmes', 'students'])
            ->withCount([
                'students',
                'students as accepted_students_count' => function ($query) {
                    return $query->where('is_accepted', '=', true);
                },
            ])
            ->get();

        $projects->each->append('course_codes');
        $projects->each->append('programme_titles');
        $projects->each->append('owner_name');
        $projects->each->append('student_names');

        return view('admin.project.index', [
            'category' => $category,
            'projects' => $projects,
        ]);
    }
}
