<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\ProjectsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    public function export($category, $format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of projects'));

        return Excel::download(new ProjectsExport($category), "uog_{$category}_project_data.{$format}");
    }
}
