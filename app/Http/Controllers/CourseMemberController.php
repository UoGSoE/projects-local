<?php

namespace App\Http\Controllers;

use App\Course;
use Illuminate\Http\Request;

class CourseMemberController extends Controller
{
    public function destroy($id)
    {
        Course::findOrFail($id)->removeAllStudents();

        return redirect()->back()->with('success', 'All students removed');
    }
}
