<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use App\Events\SomethingNoteworthyHappened;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Imports\PlacementDataExtractor;

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

            $project = Project::createFromPlacementSheet($data);
            $project = Project::firstOrCreate(['title' => $data['title']], [
                'title' => $data['title'],
                'description' => $data['description'],
                'pre_req' => $data['prereq'],
                'category' => $data['category'],
                'is_active' => $data['active'] == 'y',
                'is_placement' => $data['placement'] == 'y',
                'is_confidential' => $data['confidential'] == 'y',
                'staff_id' => $data['staff']->id,
                'max_students' => $data['max_students'],
            ]);
            $project->courses()->sync([$data['course']->id]);
            $project->programmes()->sync([$data['programme']->id]);
            if ($project->doesntHaveAcceptedStudent($data['student'])) {
                $project->addAndAccept($data['student']);
            }
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
