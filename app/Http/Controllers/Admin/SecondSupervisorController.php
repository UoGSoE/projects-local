<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Project;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use App\Http\Controllers\Controller;

class SecondSupervisorController extends Controller
{
    public function show()
    {
        return view('admin.project.import_second_supervisors');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        collect($data)->filter(function ($row) {
            return is_numeric($row[0]);
        })->each(function ($row) {
            $projectId = $row[0];
            $guid = $row[4];
            $project = Project::findOrFail($projectId);
            if (!$guid) {
                $project->update(['second_supervisor_id' => null]);
                return;
            }
            $user = User::where('username', '=', $guid)->firstOrFail();
            $project->update(['second_supervisor_id' => $user->id]);
        });

        return redirect()->back()->with('success', 'Imported OK');
    }
}
