<?php

namespace App\Http\Controllers\Admin\Exports;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\ProgrammesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProgrammeController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of programmes'));

        return Excel::download(new ProgrammesExport(), "uog_programmes.{$format}");
    }
}
