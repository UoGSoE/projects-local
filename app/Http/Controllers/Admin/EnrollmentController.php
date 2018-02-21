<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ohffs\SimpleSpout\ExcelSheet;
use App\User;

class EnrollmentController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->trimmedImport($request->file('sheet')->path());

        $course->removeAllStudents();

        collect($data)->filter(function ($row) {
            return preg_match('/^[0-9]{7}$/', $row[0]) === 1;
        })->each(function ($row) use ($course) {
            $username = $row[0] . strtolower(substr($row[1], 0, 1));
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
        });

        return redirect()->back()->with('success', 'Imported Students');
    }
}
