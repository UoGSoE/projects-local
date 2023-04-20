<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportOldProjectList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Ohffs\SimpleSpout\ExcelSheet;

class ImportOldProjectsController extends Controller
{
    public function show(): View
    {
        request()->validate([
            'category' => 'required|in:undergrad,postgrad',
        ]);

        return view('admin.import.oldprojects', [
            'category' => request()->category,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sheet' => 'required|file',
            'category' => 'required|in:undergrad,postgrad',
        ]);

        $filename = $request->sheet->store('tmp');
        $sheetData = (new ExcelSheet)->import(storage_path("app/${filename}"));

        ImportOldProjectList::dispatch($sheetData, $request->category);

        return redirect(route('home'));
    }
}
