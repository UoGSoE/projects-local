<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index()
    {
        return view('admin.project.index', [
            'projects' => Project::orderBy('title')->get(),
        ]);
    }
}