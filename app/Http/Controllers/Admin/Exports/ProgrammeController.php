<?php

namespace App\Http\Controllers\Admin\Exports;

use Illuminate\Http\Request;
use App\Exports\ProgrammesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\SomethingNoteworthyHappened;

class ProgrammeController extends Controller
{
    public function export($format)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of programmes'));
        return Excel::download(new ProgrammesExport(), "uog_programmes.{$format}");
    }
}
