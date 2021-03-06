<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Imports\PlacementDataExtractor;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Ohffs\SimpleSpout\ExcelSheet;

class PlacementController extends Controller
{
    protected $errors;

    public function show()
    {
        return view('admin.project.import_placements');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        $this->errors = new MessageBag();

        collect($data)->filter(function ($row) {
            return $this->rowHasMatric($row);
        })->each(function ($row) {
            $extractor = new PlacementDataExtractor($row);
            $data = $extractor->extract();
            if ($extractor->hasErrors()) {
                $this->errors->merge($extractor->getErrors());

                return;
            }

            Project::createFromPlacementSheet($data);
        });

        event(new SomethingNoteworthyHappened($request->user(), 'Imported placement projects'));

        return redirect()
            ->route('admin.import.placements.show')
            ->with('success', 'Imported OK')
            ->withErrors($this->errors);
    }

    protected function rowHasMatric($row)
    {
        return preg_match('/[0-9]{7}/i', $row[11]) === 1;
    }
}
