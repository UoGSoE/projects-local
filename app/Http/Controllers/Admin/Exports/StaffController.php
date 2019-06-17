<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Exports\StaffExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\SomethingNoteworthyHappened;

class StaffController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of staff'));
        return Excel::download(new StaffExport($format), 'uog_project_staff.' . $format);
    }
}
