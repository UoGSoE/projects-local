<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class ExportController extends Controller
{
    public function projects()
    {
        $filename = (new ExcelSheet)->generate($this->getProjects());

        return response()->download($filename, $this->getDownloadName())->deleteFileAfterSend(true);
    }

    protected function getProjects()
    {
        if (request()->filled('category')) {
            $query = Project::where('category', '=', request()->category);
        } else {
            $query = Project::query();
        }

        return $this->projectsToArray($query->orderBy('title')->get());
    }

    protected function getDownloadName()
    {
        if (request()->filled('category')) {
            return 'uog_' . request()->category . '_project_data.xlsx';
        }

        return 'uog_project_data.xlsx';
    }

    protected function projectsToArray($projects)
    {
        return $projects->map(function ($project, $key) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'owner_name' => $project->owner_name,
                'course_codes' => $project->course_codes,
                'category' => $project->category,
                'max_students' => $project->max_students,
                'is_active' => (boolean) $project->is_active,
                'description' => $project->description,
                'pre_req' => $project->pre_req,
            ];
        })->toArray();
    }
}
