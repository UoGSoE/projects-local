<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\User;

class ChoicesReportController extends Controller
{
    public function show(string $category): View
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
