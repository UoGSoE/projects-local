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
        collect($request->active)->reject(function ($obj) {
            return $obj['id'] == 0;
        })->each(function ($obj) {
            Project::findOrFail($obj['id'])->update(['is_active' => $obj['is_active']]);
        });

        collect($request->delete)->each(function ($projectId) {
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
