<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\User;

class ChoicesReportController extends Controller
{
    public function show(string $category)
    {
        $students = User::ofType($category)
            ->with('projects')
            ->orderBy('surname')
            ->get();

        return view('admin.user.choices', [
            'category' => $category,
            'students' => $students,
        ]);
    }
}
