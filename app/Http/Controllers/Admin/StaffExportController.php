<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Exports\StaffListExporter;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;

class StaffExportController extends Controller
{
    public function export()
    {
        $staff = User::staff()
            ->with(['staffProjects.students', 'secondSupervisorProjects'])
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return $user->getProjectStats();
            });

        $filename = (new StaffListExporter($staff))->create();

        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of staff'));

        return response()->download($filename, 'uog_project_staff.xlsx')->deleteFileAfterSend(true);
    }
}
