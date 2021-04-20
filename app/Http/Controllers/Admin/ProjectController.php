<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index($category = 'undergrad')
    {
        $type = request()->type ?: null;
        $programmeFilter = request()->programme ?: '';
        $projects = Project::where('category', '=', $category)
            ->orderBy('title')
            ->with(['owner', 'secondSupervisor', 'courses', 'programmes', 'students'])
            ->when($type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($programmeFilter, function ($query, $programmeFilter) {
                return $query->whereHas('programmes', function ($query) use ($programmeFilter) {
                    $query->where('title', $programmeFilter);
                });
            })
            ->withCount([
                'students',
                'students as accepted_students_count' => function ($query) {
                    return $query->where('is_accepted', '=', true);
                },
            ])
            ->get();
        $programmes = Programme::orderBy('title')->get();

        $projects->each->append('course_codes');
        $projects->each->append('programme_titles');
        $projects->each->append('owner_name');
        $projects->each->append('student_names');

        return view('admin.project.index', [
            'category' => $category,
            'projects' => $projects,
            'type' => $type,
            'programmes' => $programmes,
            'programmeFilter' => $programmeFilter
        ]);
    }
}
