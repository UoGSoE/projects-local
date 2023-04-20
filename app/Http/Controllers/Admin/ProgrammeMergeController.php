<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProgrammeMergeController extends Controller
{
    public function index()
    {
        return view('admin.programme.merge');
    }
}
