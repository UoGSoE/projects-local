<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Imports\AllocationsImport;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ImportAllocationController extends Controller
{
    protected $errors;

    public function show(): View
    {
        return view('admin.project.import_allocations');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $import = new AllocationsImport();
        $import->import($request->file('sheet'));
        $errors = new MessageBag();
        foreach ($import->failures() as $failure) {
            if ($failure->attribute() === 'guid') {
                $errors->add('usernotfound-'.$failure->values()['guid'], 'Student Not Found : '.$failure->values()['guid'].' / row '.$failure->row());
            } elseif ($failure->attribute() === 'project_id') {
                $errors->add('projectnotfound-'.$failure->values()['project_id'], 'Project Not Found : '.$failure->values()['project_id'].' / row '.$failure->row());
            }
        }

        event(new SomethingNoteworthyHappened($request->user(), 'Imported project allocations'));

        return redirect()->back()->with('success', 'Imported OK')->withErrors($errors);
    }
}
