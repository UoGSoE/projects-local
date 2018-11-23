<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Ohffs\SimpleSpout\ExcelSheet;

class ImportAllocationController extends Controller
{
    protected $errors;

    public function show()
    {
        return view('admin.project.import_allocations');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        $this->errors = new MessageBag();

        collect($data)->filter(function ($row) {
            return $this->isStudentGuid($row[0]);
        })->each(function ($row) {
            $projectId = $row[2];
            $guid = strtolower($row[0]);
            $project = Project::find($projectId);
            if (!$project) {
                $this->errors->add("projectnotfound-{$projectId}", "Project Not Found : {$projectId} / {$row[0]}");
                return;
            }
            // disable the project events being logged so we don't spam the activity log
            $project->unsetEventDispatcher();
            $student = User::students()->where('username', '=', $guid)->first();
            if (!$student) {
                $this->errors->add("usernotfound-{$guid}", "Student Not Found : {$guid}");
                return;
            }
            $project->addAndAccept($student);
        });

        event(new SomethingNoteworthyHappened($request->user(), 'Imported project allocations'));

        return redirect()->back()->with('success', 'Imported OK')->withErrors($this->errors);
    }

    private function isStudentGuid($guid)
    {
        return preg_match('/^[0-9]{7}[a-z]$/i', $guid) === 1;
    }
}
