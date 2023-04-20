<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\User;

class ChoiceController extends Controller
{
    public function index($category): View
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
