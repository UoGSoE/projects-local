<?php

namespace App\Exports;

use Ohffs\SimpleSpout\ExcelSheet;

class ProjectListExporter
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function create()
    {
        return (new ExcelSheet)->generate($this->projectsToArray());
    }

    protected function projectsToArray()
    {
        return $this->projects->map(function ($project, $key) {
            $acceptedStudents = $project->students()->where('is_accepted', '=', true)->get();
            $row = [
                'id' => $project->id,
                'title' => $project->title,
                'owner_guid' => $project->owner->username,
                'owner_name' => $project->owner_name,
                '2nd_supervisor_guid' => optional($project->secondSupervisor)->username,
                '2nd_supervisor_name' => optional($project->secondSupervisor)->full_name,
                'course_codes' => $project->course_codes,
                'category' => $project->category,
                'max_students' => $project->max_students,
                'is_active' => $project->is_active ? 'Y' : 'N',
                'is_confidential' => $project->is_confidential ? 'Y' : 'N',
                'is_placement' => $project->is_placement ? 'Y' : 'N',
                'description' => $project->description,
                'pre_req' => $project->pre_req,
            ];
            foreach ($acceptedStudents as $student) {
                $row[] = $student->full_name;
            }

            return $row;
        })->prepend([
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
        ])->toArray();
    }
}
