<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\Jobs\ImportStudents;
use App\Models\Course;
use Illuminate\Http\Request;
use Ohffs\SimpleSpout\ExcelSheet;

class EnrollmentController extends Controller
{
    public function create(Course $course): View
    {
        return view('admin.course.enrollment', [
            'course' => $course,
        ]);
    }

    public function store(Course $course, Request $request): RedirectResponse
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        ImportStudents::dispatch($data, $course, auth()->user());

        event(new SomethingNoteworthyHappened($request->user(), "Uploaded students to be enrolled on {$course->code} spreadsheet"));

        return redirect()->route('admin.course.show', $course->id)
            ->with('success', 'Importing students. This may take some time. You will be emailed once it is finished.');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->removeAllStudents();

        event(new SomethingNoteworthyHappened(auth()->user(), "Removed all students from {$course->code}"));

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Students Removed',
            ]);
        }

        return redirect()->back()->with('success', 'All students removed');
    }
}
