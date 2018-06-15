<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Exports\ProjectListExporter;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class ExportController extends Controller
{
    public function projects()
    {
        $filename = (new ProjectListExporter($this->getProjects()))->create();

        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of projects'));

        return response()->download($filename, $this->getDownloadName())->deleteFileAfterSend(true);
    }

    protected function getProjects()
    {
        if (request()->filled('category')) {
            $query = Project::where('category', '=', request()->category);
        } else {
            $query = Project::query();
        }

        return $query->orderBy('title')->get();
    }

    protected function getDownloadName()
    {
        if (request()->filled('category')) {
            return 'uog_' . request()->category . '_project_data.xlsx';
        }

        return 'uog_project_data.xlsx';
    }
}
