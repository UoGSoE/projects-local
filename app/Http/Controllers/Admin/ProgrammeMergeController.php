<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProgrammeMergeController extends Controller
{
    public function index(): View
    {
        return view('admin.programme.merge');
    }
}
