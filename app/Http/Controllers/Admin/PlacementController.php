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
            $data = $this->extractCells($row);

            if (!$data['staff'] or !$data['student'] or !$data['course'] or !$data['programme']) {
                return;
            }

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

    protected function extractCells($row)
    {
        $data = [
            'category' => strtolower($row[0]),
            'title' => $row[1],
            'description' => $row[2],
            'prereq' => $row[3],
            'active' => substr(strtolower($row[4]), 0, 1),
            'placement' => substr(strtolower($row[5]), 0, 1),
            'confidential' => substr(strtolower($row[6]), 0, 1),
            'guid' => strtolower($row[7]),
            'max_students' => $row[8],
            'courseCode' => strtoupper($row[9]),
            'programmeName' => $row[10],
            'matric' => $row[11],
            'surname' => strtolower($row[12]),
        ];
        $data['staff'] = $this->findStaff($data['guid']);
        $data['student'] = $this->findStudent($data['matric'], $data['surname']);
        $data['course'] = $this->findCourse($data['courseCode'], $data['category']);
        $data['programme'] = $this->findProgramme($data['programmeName'], $data['category']);
        return $data;
    }

    protected function findStaff($guid)
    {
        $staff = User::where('username', '=', $guid)->first();
        if (!$staff) {
            $this->errors->add("staffnotfound-{$guid}", "Staff Not Found : {$guid}");
        }
        return $staff;
    }

    protected function findStudent($matric, $surname)
    {
        $studentGuid = $matric . strtolower(substr($surname, 0, 1));
        $student = User::where('username', '=', $studentGuid)->first();
        if (!$student) {
            $this->errors->add("studentnotfound-{$studentGuid}", "Student Not Found : {$studentGuid}");
        }
        return $student;
    }

    protected function findCourse($code, $category)
    {
        $course = Course::where('code', '=', $code)->where('category', '=', $category)->first();
        if (!$course) {
            $this->errors->add("coursenotfound-{$code}", "Course Not Found : {$code}");
        }
        return $course;
    }

    protected function findProgramme($title, $category)
    {
        $programme = Programme::where('title', '=', $title)->where('category', '=', $category)->first();
        if (!$programme) {
            $this->errors->add("programmenotfound-{$title}", "Programme Not Found : {$title}");
        }
        return $programme;
    }
}
