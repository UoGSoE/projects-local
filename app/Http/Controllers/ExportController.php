<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class ExportController extends Controller
{
    public function projects()
    {
        $projects = Project::orderBy('title')->get()->toArray();
        $filename = (new ExcelSheet)->generate($projects);

        return response()->download($filename, 'uog_project_data.xlsx')->deleteFileAfterSend(true);
    }
}
