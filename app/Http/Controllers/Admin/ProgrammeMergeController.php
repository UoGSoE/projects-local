<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

class ProgrammeMergeController extends Controller
{
    public function index(): View
    {
        return view('admin.programme.merge');
    }
}
