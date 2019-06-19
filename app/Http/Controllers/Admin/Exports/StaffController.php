<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\SomethingNoteworthyHappened;
use App\Exports\StaffExport;

class StaffController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of students'));
        return Excel::download(new StaffExport, "uog_project_staff." . $format);
    }
}
