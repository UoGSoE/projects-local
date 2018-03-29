<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChoiceController extends Controller
{
    public function index($category)
    {
        return view('admin.student.choices', [
            'category' => $category,
            'students' => User::students()
                                ->whereHas('projects', function ($query) use ($category) {
                                    $query->where('category', '=', $category);
                                })
                                ->with(['projects' => function ($query) use ($category) {
                                    $query->where('category', '=', $category);
                                }])
                                ->orderBy('surname')
                                ->get(),
        ]);
    }
}
