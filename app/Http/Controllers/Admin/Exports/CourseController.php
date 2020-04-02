<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CourseController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of courses'));

        return Excel::download(new CoursesExport(), "uog_courses.{$format}");
    }
}
