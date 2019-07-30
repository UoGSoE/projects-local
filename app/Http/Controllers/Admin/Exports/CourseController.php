<?php

namespace App\Http\Controllers\Admin\Exports;

use Illuminate\Http\Request;
use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\SomethingNoteworthyHappened;

class CourseController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of courses'));
        return Excel::download(new CoursesExport(), "uog_courses.{$format}");
    }
}
