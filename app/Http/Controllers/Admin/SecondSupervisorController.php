<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Ohffs\SimpleSpout\ExcelSheet;

class SecondSupervisorController extends Controller
{
    protected $errors;

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

        $this->errors = new MessageBag();

        collect($data)->filter(function ($row) {
            return is_numeric($row[0]);
        })->each(function ($row) {
            $projectId = $row[0];
            $guid = $row[4];
            $project = Project::find($projectId);
            if (! $project) {
                $this->errors->add("projectnotfound-{$projectId}", "Project Not Found : {$projectId} / {$row[1]}");

                return;
            }
            // disable the project events being logged so we don't spam the activity log
            $project->unsetEventDispatcher();
            if (! $guid) {
                $project->update(['second_supervisor_id' => null]);

                return;
            }
            $user = User::where('username', '=', $guid)->first();
            if (! $user) {
                $this->errors->add("usernotfound-{$guid}", "User Not Found : {$guid}");

                return;
            }
            $project->update(['second_supervisor_id' => $user->id]);
        });

        event(new SomethingNoteworthyHappened($request->user(), 'Imported 2nd supervisors'));

        return redirect()->back()->with('success', 'Imported OK')->withErrors($this->errors);
    }
}
