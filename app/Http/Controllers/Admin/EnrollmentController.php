<?php

namespace App\Http\Controllers\Admin;

use App\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ohffs\SimpleSpout\ExcelSheet;

class EnrollmentController extends Controller
{
    public function store(Course $course, Request $request)
    {
        $request->validate([
            'sheet' => 'required|file',
        ]);

        $data = (new ExcelSheet)->import($request->file('sheet')->path());

        dd($data);
    }
}
