<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\StaffExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of students'));

        return Excel::download(new StaffExport, 'uog_project_staff.'.$format);
    }
}
