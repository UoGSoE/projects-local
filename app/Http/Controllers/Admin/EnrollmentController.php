<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use App\Events\SomethingNoteworthyHappened;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ohffs\SimpleSpout\ExcelSheet;

class EnrollmentController extends Controller
{
    public function create(Course $course)
    {
        return view('admin.course.enrollment', [
            'course' => $course,
        ]);
    }

    public function store(Course $course, Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        $course->removeAllStudents();
        $students = $course->enrollStudents($data);

        event(new SomethingNoteworthyHappened($request->user(), "Enrolled students onto {$course->code}"));

        return redirect()->route('admin.course.show', $course->id)
            ->with('success', "Imported {$students->count()} Students");
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
