<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportOldProjectList;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class ImportOldProjectsController extends Controller
{
    public function show()
    {
        return view('admin.import.oldprojects');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $filename = $request->sheet->store('tmp');
        $sheetData = (new ExcelSheet)->import(storage_path("app/${filename}"));

        ImportOldProjectList::dispatch($sheetData);

        return redirect(route('home'));
    }
}
