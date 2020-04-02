<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function undergrad($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of students'));

        return Excel::download(new StudentsExport('undergrad'), 'uog_undergrad_project_students.'.$format);
    }

    public function postgrad($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of students'));

        return Excel::download(new StudentsExport('postgrad'), 'uog_postgrad_project_students.'.$format);
    }
}
