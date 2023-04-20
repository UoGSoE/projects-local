<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportDmoranSheetRow;
use App\Mail\DMoranSpreadsheetImportCompleteMail;
use App\Models\Project;
use App\Models\User;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

class DaveMoranImportController extends Controller
{
    public function show()
    {
        return view('admin.import.davemoran');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($request->file('sheet')->getPathname());

        Project::activeMeng()->delete();

        $userId = $request->user()->id;
        $batch = Bus::batch([]);

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getIndex() === 0) { // index is 0-based
                $rowNumber = 1;
                foreach ($sheet->getRowIterator() as $row) {
                    $batch->add([new ImportDmoranSheetRow($row->toArray(), $rowNumber)]);
                    $rowNumber = $rowNumber + 1;
                }
                break; // no need to read more sheets
            }
        }

        $batch->allowFailures()->finally(function ($batch) use ($userId) {
            // send email to User::find($userId)
            $user = User::find($userId);
            Mail::to($user)->queue(new DMoranSpreadsheetImportCompleteMail($batch->id.'-errors'));
        })->dispatch();

        $reader->close();

        return redirect()->route('home')->with('success', 'Import started');
    }
}
