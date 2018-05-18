<?php

namespace App\Http\Controllers\Admin;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectOptionsController extends Controller
{
    public function index($category)
    {
        $projects = Project::where('category', '=', $category)
            ->orderBy('title')
            ->with(['owner', 'secondSupervisor', 'courses'])
            ->get();

        $projects->each->append('course_codes');

        return view('admin.project.options.index', [
            'category' => $category,
            'projects' => $projects,
        ]);
    }

    public function update($category, Request $request)
    {
        $request->validate([
            'active' => 'array',
            'delete' => 'array',
        ]);

        collect($request->active)->each(function ($newValue, $projectId) {
            Project::findOrFail($projectId)->update(['is_active' => $newValue]);
        });

        collect($request->delete)->filter(function ($flag, $projectId) {
            return $flag == 1;
        })->each(function ($flag, $projectId) {
            Project::findorFail($projectId)->delete();
        });

        session()->flash('success', 'Updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Updated",
            ]);
        }

        return redirect()->route('admin.project.index', ['category' => $category]);
    }
}
