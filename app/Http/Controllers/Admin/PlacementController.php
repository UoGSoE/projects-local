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
            // ignore any rows without a student matric
            return preg_match('/[0-9]{7}/i', $row[11]) === 1;
        })->each(function ($row) {
            [$category, $title, $description, $prereq, $active, $placement, $confidential, $guid, $max_students, $courseCode, $programmeName, $matric, $surname] = $row;
            $staff = User::where('username', '=', $guid)->first();
            if (!$staff) {
                $this->errors->add("staffnotfound-{$guid}", "Staff Not Found : {$guid}");
                return;
            }
            $studentGuid = $matric . strtolower(substr($surname, 0, 1));
            $student = User::where('username', '=', $studentGuid)->first();
            if (!$student) {
                $this->errors->add("studentnotfound-{$studentGuid}", "Student Not Found : {$studentGuid}");
                return;
            }
            $course = Course::where('code', '=', $courseCode)->where('category', '=', $category)->first();
            if (!$course) {
                $this->errors->add("coursenotfound-{$courseCode}", "Course Not Found : {$courseCode}");
                return;
            }
            $programme = Programme::where('title', '=', $programmeName)->where('category', '=', $category)->first();
            if (!$programme) {
                $this->errors->add("programmenotfound-{$programmeName}", "Programme Not Found : {$programmeName}");
                return;
            }
            $project = Project::create([
                'title' => $title,
                'description' => $description,
                'pre_req' => $prereq,
                'category' => $category,
                'is_active' => $active == 'Yes',
                'is_placement' => $placement == 'Yes',
                'is_confidential' => $confidential == 'Yes',
                'staff_id' => $staff->id,
                'max_students' => $max_students,
            ]);
            $project->courses()->sync([$course->id]);
            $project->programmes()->sync([$programme->id]);
            $project->addAndAccept($student);
        });

        event(new SomethingNoteworthyHappened($request->user(), 'Imported placement projects'));

        return redirect()
            ->route('admin.import.placements.show')
            ->with('success', 'Imported OK')
            ->withErrors($this->errors);
    }
}
