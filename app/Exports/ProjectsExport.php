<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProjectsExport implements FromCollection
{
    public function __construct($category, $type, $programmeFilter)
    {
        $this->category = $category; //Undergrad or Postgrad
        $this->type = $type; // B.Eng, M.Eng or UESTC
        $this->programmeFilter = $programmeFilter;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $type = $this->type;
        $programmeFilter = $this->programmeFilter;

        $projects = Project::where('category', '=', $this->category)
            ->when($type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($programmeFilter, function ($query, $programmeFilter) {
                return $query->whereHas('programmes', function ($query) use ($programmeFilter) {
                    $query->where('title', $programmeFilter);
                });
            })
            ->orderBy('title')
            ->get()
            ->map(function ($project, $key) {
                $acceptedStudents = $project->students()->where('is_accepted', '=', true)->get();
                $row = [
                    'id' => $project->id,
                    'title' => $project->title,
                    'owner_guid' => $project->owner->username,
                    'owner_name' => $project->owner_name,
                    '2nd_supervisor_guid' => $project->secondSupervisor?->username,
                    '2nd_supervisor_name' => $project->secondSupervisor?->full_name,
                    'course_codes' => $project->course_codes,
                    'category' => $project->category,
                    'max_students' => $project->max_students,
                    'is_active' => $project->is_active ? 'Y' : 'N',
                    'is_confidential' => $project->is_confidential ? 'Y' : 'N',
                    'is_placement' => $project->is_placement ? 'Y' : 'N',
                    'description' => $project->description,
                    'pre_req' => $project->pre_req,
                    'programmes' => $project->programmes->pluck('title')->implode('|'),
                    'created_at' => $project->created_at->format('d/m/Y'),
                ];
                foreach ($acceptedStudents as $key => $student) {
                    $row['student_'.($key + 1)] = $student->full_name;
                }

                return $row;
            })
            ->prepend([
                'ID',
                'Title',
                'Owner GUID',
                'Owner Name',
                '2nd Sup GUID',
                '2nd Sup Name',
                'Courses',
                'Category',
                'Max Students',
                'Active?',
                'Confidential?',
                'Placement?',
                'Description',
                'Pre-reqs',
                'Programmes',
                'Created',
            ]);

        return $projects;
    }
}
