<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Imports\AllocationsImport;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

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

        $import = new AllocationsImport();
        $import->import($request->file('sheet'));
        $errors = new MessageBag();
        foreach ($import->failures() as $failure) {
            $errors->add($failure->errors()[0][0], $failure->errors()[0][1]." / row {$failure->row()}");
        }

        event(new SomethingNoteworthyHappened($request->user(), 'Imported project allocations'));

        return redirect()->back()->with('success', 'Imported OK')->withErrors($errors);
    }
}
