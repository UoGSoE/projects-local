<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ohffs\SimpleSpout\ExcelSheet;
use App\User;

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

        $students = collect($data)->filter(function ($row) {
            return $this->firstColumnIsAMatric($row);
        })->map(function ($row) use ($course) {
            $username = $this->joinMatricAndFirstInitial($row);
            $user = User::where('username', '=', $username)->first();
            if (! $user) {
                $user = new User([
                    'username' => $username,
                    'password' => bcrypt(str_random(64)),
                ]);
            }
            $user->surname = $row[1];
            $user->forenames = $row[2];
            $user->email = $row[3];
            $user->course_id = $course->id;
            $user->save();
            return $user->id;
        });

        return redirect()->route('admin.course.show', $course->id)->with('success', "Imported {$students->count()} Students");
    }

    protected function firstColumnIsAMatric($row)
    {
        return preg_match('/^[0-9]{7}$/', $row[0]) === 1;
    }

    protected function joinMatricAndFirstInitial($row)
    {
        return $row[0] . strtolower(substr($row[1], 0, 1));
    }
}
