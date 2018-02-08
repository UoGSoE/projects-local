<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChoiceController extends Controller
{
    public function index()
    {
        return view('admin.student.choices', [
            'students' => User::students()->with('projects')->orderBy('surname')->get(),
        ]);
    }
}
